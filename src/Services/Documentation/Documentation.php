<?php

/**
 * @ Created on 07/03/2023 17:17
 * @ This file is part of the NetagriWeb project.
 * @ Contact (c) Valentin Charbonneau <valentincharbonneau@outlook.fr>
 * @ Licence For the full copyright and license information, please view the LICENSE
 */

declare(strict_types=1);

namespace App\Services\Documentation;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Class Documentation.
 *
 * @author Valentin Charbonneau <valentincharbonneau@outlook.fr>
 */
class Documentation implements DocumentationInterface
{
    public function __construct(
        private ParameterBagInterface $bag,
        private RouterInterface $routing
    ) {
    }

    public function build() : array
    {
        $dir = $this->bag->get("kernel.project_dir");

        $groups = [];
        $api = [];

        foreach (scandir($dir . "/config/serialization") as $file) {
            $fileExplode = explode(".", $file);
            if (strtolower($fileExplode[count($fileExplode) - 1]) == "yaml") {
                $yamlValue = Yaml::parseFile($dir . "/config/serialization/" . $file);
                $groups[array_key_first($yamlValue)] = $yamlValue[array_key_first($yamlValue)];
            }
        }
        foreach (scandir($dir . "/config/documentation_resources") as $file) {
            $fileExplode = explode(".", $file);
            if (strtolower($fileExplode[count($fileExplode) - 1]) == "yaml") {
                $api[ucfirst($fileExplode[0])] = Yaml::parseFile($dir . "/config/documentation_resources/" . $file);
            }
        }

        $result = [];

        foreach ($api as $field => $content) {
            $entity = array_key_first($content);
            $content = $content[$entity];

            $result[$field] = [];
            $result[$field]['admin'] = $content['admin'];

            if (array_key_exists('entity', $content) && $content['entity'] == false) {
                foreach($content["endpoints"] as $endpointName => $endpoint) {
                    $result[$field]["endpoints"][$endpointName]["header_path"] = $this->routing->getRouteCollection()->get($endpointName)->getPath();
                    $result[$field]["endpoints"][$endpointName]["header_method"] = $this->routing->getRouteCollection()->get($endpointName)->getMethods()[0];
                    if (array_key_exists('multiple_entity', $endpoint)) {
                        $result[$field]["endpoints"][$endpointName]["pagination"] = $endpoint['multiple_entity'];
                    } else {
                        $result[$field]["endpoints"][$endpointName]["pagination"] = false;
                    }

                    $input = [];
                    if (array_key_exists('input_group', $endpoint)) {
                        foreach ($endpoint['input_group'] as $entityName => $entityGroup) {
                            $new = [];
                            foreach ($api as $apiEntityName) {
                                if (array_key_first($apiEntityName) == $entityName) {
                                    foreach($apiEntityName[array_key_first($apiEntityName)]["attributes"] as $attribute => $value) {
                                        if (in_array($entityGroup, $groups[$entityName]["attributes"][$attribute]["groups"])) {
                                            if ($this->isJson($apiEntityName[array_key_first($apiEntityName)]['attributes'][$attribute]["exemple"])) {
                                                $new[$attribute] = json_decode($apiEntityName[array_key_first($apiEntityName)]['attributes'][$attribute]["exemple"], true);
                                            } else {
                                                $new[$attribute] = $apiEntityName[array_key_first($apiEntityName)]['attributes'][$attribute]["exemple"];
                                            }
                                        }
                                    }

                                    break;
                                }
                            }
                            $input[] = $new;
                        }
                    }

                    $output = [];
                    if (array_key_exists('output_group', $endpoint)) {
                        foreach ($endpoint['output_group'] as $entityName => $entityGroup) {
                            $new = [];
                            foreach ($api as $apiEntityName) {
                                if (array_key_first($apiEntityName) == $entityName) {
                                    foreach($apiEntityName[array_key_first($apiEntityName)]["attributes"] as $attribute => $value) {
                                        if (in_array($entityGroup, $groups[$entityName]["attributes"][$attribute]["groups"])) {
                                            if ($this->isJson($apiEntityName[array_key_first($apiEntityName)]['attributes'][$attribute]["exemple"])) {
                                                $new[$attribute] = json_decode($apiEntityName[array_key_first($apiEntityName)]['attributes'][$attribute]["exemple"], true);
                                            } else {
                                                $new[$attribute] = $apiEntityName[array_key_first($apiEntityName)]['attributes'][$attribute]["exemple"];
                                            }
                                        }
                                    }

                                    break;
                                }
                            }
                            $output[] = $new;
                        }
                    }

                    if (array_key_exists('output_code', $endpoint)) {
                        $result[$field]["endpoints"][$endpointName]['output_code'] = $endpoint["output_code"];
                    } else {
                        $result[$field]["endpoints"][$endpointName]['output_code'] = 200;
                    }

                    $result[$field]["endpoints"][$endpointName]['input'] = $input;
                    $result[$field]["endpoints"][$endpointName]['output'] = $output;
                    $result[$field]["endpoints"][$endpointName]['error'] = $endpoint['error_code'];
                }
            } else {
                foreach($content["endpoints"] as $endpointName => $endpoint) {
                    if (array_key_exists('multiple_entity', $endpoint)) {
                        $result[$field]["endpoints"][$endpointName]["pagination"] = $endpoint['multiple_entity'];
                    } else {
                        $result[$field]["endpoints"][$endpointName]["pagination"] = false;
                    }
                    $result[$field]["endpoints"][$endpointName]["header_path"] = $this->routing->getRouteCollection()->get($endpointName)->getPath();
                    $result[$field]["endpoints"][$endpointName]["header_method"] = $this->routing->getRouteCollection()->get($endpointName)->getMethods()[0];

                    $outputEntity = $entity;
                    if (array_key_exists('output_entity', $endpoint)) {
                        $outputEntity = $endpoint['output_entity'];
                    }

                    $input = [];
                    if (array_key_exists('input_group', $endpoint)) {
                        foreach($content["attributes"] as $attribute => $value) {
                            if (in_array($endpoint["input_group"], $groups[$entity]["attributes"][$attribute]["groups"])) {
                                if ($this->isJson($value["exemple"])) {
                                    $input[$attribute] = json_decode($value["exemple"], true);
                                } else {
                                    $input[$attribute] = $value["exemple"];
                                }
                            }
                        }
                    }

                    $output = [];
                    if (array_key_exists('output_group', $endpoint)) {
                        if ($entity == $outputEntity) {
                            foreach($content["attributes"] as $attribute => $value) {
                                if (in_array($endpoint["output_group"], $groups[$outputEntity]["attributes"][$attribute]["groups"])) {
                                    if ($this->isJson($value["exemple"])) {
                                        $output[$attribute] = json_decode($value["exemple"], true);
                                    } else {
                                        $output[$attribute] = $value["exemple"];
                                    }
                                }
                            }
                        } else {
                            foreach ($api as $entityName) {
                                if (array_key_first($entityName) == $outputEntity) {
                                    foreach($entityName[array_key_first($entityName)]["attributes"] as $attribute => $value) {
                                        if (in_array($endpoint["output_group"], $groups[$outputEntity]["attributes"][$attribute]["groups"])) {
                                            if ($this->isJson($entityName[array_key_first($entityName)]['attributes'][$attribute]["exemple"])) {
                                                $output[$attribute] = json_decode($entityName[array_key_first($entityName)]['attributes'][$attribute]["exemple"], true);
                                            } else {
                                                $output[$attribute] = $entityName[array_key_first($entityName)]['attributes'][$attribute]["exemple"];
                                            }
                                        }
                                    }

                                    break;
                                }
                            }
                        }
                        if (array_key_exists('multiple_entity', $endpoint) && $endpoint["multiple_entity"]) {
                            $output = [$output];
                            $result[$field]["endpoints"][$endpointName]["header_path"] = rtrim($result[$field]["endpoints"][$endpointName]["header_path"], "/") . "[?page=1]";
                        }
                    }

                    if (array_key_exists('output_code', $endpoint)) {
                        $result[$field]["endpoints"][$endpointName]['output_code'] = $endpoint["output_code"];
                    } else {
                        $result[$field]["endpoints"][$endpointName]['output_code'] = 200;
                    }

                    $result[$field]["endpoints"][$endpointName]['input'] = $input;
                    $result[$field]["endpoints"][$endpointName]['output'] = $output;
                    $result[$field]["endpoints"][$endpointName]['error'] = $endpoint['error_code'];
                }
            }
        }

        $return = [];
        $return["pagination"] = $this->bag->get("ged_pagination");
        $return["result"] = $result;

        return $return;
    }

    private function isJson($string) {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
     }
}
