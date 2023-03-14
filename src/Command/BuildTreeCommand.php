<?php

/**
 * @ Created on 09/03/2023 14:54
 * @ This file is part of the Open WEB Storage project.
 * @ Contact (c) Valentin Charbonneau <valentincharbonneau@outlook.fr>
 * @ Licence For the full copyright and license information, please view the LICENSE
 */

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(name: 'ows:build-tree')]
class BuildTreeCommand extends Command
{
    protected function configure(): void
    {
        $this->setHelp('This command build tree to store documents and archives.');
    }

    public function __construct(
        private ParameterBagInterface $bag
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->bag->has("ged_dir") || !$this->bag->has("archive_dir") || !$this->bag->has("doc_dir") || !$this->bag->has("key_dir")) {
            if (!$this->bag->has("ged_dir")) {
                $output->writeln('<error>"ged_dir" parameter must be defined on config/services.yaml</error>');
            }
            if (!$this->bag->has("archive_dir")) {
                $output->writeln('<error>"archive_dir" parameter must be defined on config/services.yaml</error>');
            }
            if (!$this->bag->has("doc_dir")) {
                $output->writeln('<error>"doc_dir" parameter must be defined on config/services.yaml</error>');
            }
            if (!$this->bag->has("key_dir")) {
                $output->writeln('<error>"key_dir" parameter must be defined on config/services.yaml</error>');
            }

            return Command::FAILURE;
        }

        $dir = preg_replace('/\\\\/i', "/", $this->bag->get("ged_dir"));
        $file = preg_replace('/\\\\/i', "/", $this->bag->get("archive_dir"));
        $arch = preg_replace('/\\\\/i', "/", $this->bag->get("doc_dir"));
        $key = preg_replace('/\\\\/i', "/", $this->bag->get("key_dir"));

        $dirParent = explode("/", $dir);
        array_pop($dirParent);
        $dirParent = implode("/", $dirParent);
        $fileParent = explode("/", $file);
        array_pop($fileParent);
        $fileParent = implode("/", $fileParent);
        $archParent = explode("/", $arch);
        array_pop($archParent);
        $archParent = implode("/", $archParent);
        $keyParent = explode("/", $key);
        array_pop($keyParent);
        $keyParent = implode("/", $keyParent);

        if (!file_exists($dirParent)) {
            $output->writeln('<error>Folder ' . $dirParent . ' not found</error>');
            return Command::FAILURE;
        }
        if (!is_writable($dirParent)) {
            $output->writeln('<error>Folder ' . $dirParent . ' is not writable</error>');
            return Command::FAILURE;
        }
        if (!file_exists($dir)) {
            mkdir($dir, 0550);
        }

        if (!file_exists($fileParent)) {
            $output->writeln('<error>Folder ' . $fileParent . ' not found</error>');
            return Command::FAILURE;
        }
        if (!is_writable($fileParent)) {
            $output->writeln('<error>Folder ' . $fileParent . ' is not writable</error>');
            return Command::FAILURE;
        }
        if (!file_exists($file)) {
            mkdir($file, 0550);
        }

        if (!file_exists($archParent)) {
            $output->writeln('<error>Folder ' . $archParent . ' not found</error>');
            return Command::FAILURE;
        }
        if (!is_writable($archParent)) {
            $output->writeln('<error>Folder ' . $archParent . ' is not writable</error>');
            return Command::FAILURE;
        }
        if (!file_exists($arch)) {
            mkdir($arch, 0550);
        }

        if (!file_exists($keyParent)) {
            $output->writeln('<error>Folder ' . $keyParent . ' not found</error>');
            return Command::FAILURE;
        }
        if (!is_writable($keyParent)) {
            $output->writeln('<error>Folder ' . $keyParent . ' is not writable</error>');
            return Command::FAILURE;
        }
        if (!file_exists($key)) {
            mkdir($key, 0550);
        }

        $output->writeln('<info>Success</info>');

        return Command::SUCCESS;
    }
}
