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
    private const BEGIN_ERROR_FOLDER = "<error>Folder ";
    private const END_ERROR_NOT_WRITABLE = " is not writable</error>";
    private const END_ERROR_NOT_FOUND = " not found</error>";

    private string $dirParent;
    private string $fileParent;
    private string $archParent;
    private string $keyParent;
    private string $dir;
    private string $file;
    private string $arch;
    private string $key;

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

        $this->dir = str_replace("\\\\", "/", $this->bag->get("ged_dir"));
        $this->file = str_replace("\\\\", "/", $this->bag->get("archive_dir"));
        $this->arch = str_replace("\\\\", "/", $this->bag->get("doc_dir"));
        $this->key = str_replace("\\\\", "/", $this->bag->get("key_dir"));

        $arrayDirParent = explode("/", $this->dir);
        array_pop($arrayDirParent);
        $this->dirParent = implode("/", $arrayDirParent);
        $arrayFileParent = explode("/", $this->file);
        array_pop($arrayFileParent);
        $this->fileParent = implode("/", $arrayFileParent);
        $arrayArchParent = explode("/", $this->arch);
        array_pop($arrayArchParent);
        $this->archParent = implode("/", $arrayArchParent);
        $arrayKeyParent = explode("/", $this->key);
        array_pop($arrayKeyParent);
        $this->keyParent = implode("/", $arrayKeyParent);

        return $this->verification($output);
    }

    private function verification(OutputInterface $output): int
    {
        $result = Command::SUCCESS;

        $this->verifyDir($output, $result);
        $this->verifyFile($output, $result);
        $this->verifyArch($output, $result);
        $this->verifyKey($output, $result);

        if ($result == Command::SUCCESS) {
            $output->writeln('<info>Success</info>');
        }

        return $result;
    }

    private function verifyDir(OutputInterface $output, int &$currentResult): void
    {
        if (!file_exists($this->dirParent)) {
            $output->writeln(BuildTreeCommand::BEGIN_ERROR_FOLDER . $this->dirParent . BuildTreeCommand::END_ERROR_NOT_FOUND);
            $currentResult = Command::FAILURE;
        }
        if (!is_writable($this->dirParent)) {
            $output->writeln(BuildTreeCommand::BEGIN_ERROR_FOLDER . $this->dirParent . BuildTreeCommand::END_ERROR_NOT_WRITABLE);
            $currentResult = Command::FAILURE;
        }
        if (!file_exists($this->dir) && $currentResult == Command::SUCCESS) {
            mkdir($this->dir, 0550);
        }
    }

    private function verifyFile(OutputInterface $output, int &$currentResult): void
    {
        if (!file_exists($this->fileParent)) {
            $output->writeln(BuildTreeCommand::BEGIN_ERROR_FOLDER . $this->fileParent . BuildTreeCommand::END_ERROR_NOT_FOUND);
            $currentResult = Command::FAILURE;
        }
        if (!is_writable($this->fileParent)) {
            $output->writeln(BuildTreeCommand::BEGIN_ERROR_FOLDER . $this->fileParent . BuildTreeCommand::END_ERROR_NOT_WRITABLE);
            $currentResult = Command::FAILURE;
        }
        if (!file_exists($this->file) && $currentResult == Command::SUCCESS) {
            mkdir($this->file, 0550);
        }
    }

    private function verifyArch(OutputInterface $output, int &$currentResult): void
    {
        if (!file_exists($this->archParent)) {
            $output->writeln(BuildTreeCommand::BEGIN_ERROR_FOLDER . $this->archParent . BuildTreeCommand::END_ERROR_NOT_FOUND);
            $currentResult = Command::FAILURE;
        }
        if (!is_writable($this->archParent)) {
            $output->writeln(BuildTreeCommand::BEGIN_ERROR_FOLDER . $this->archParent . BuildTreeCommand::END_ERROR_NOT_WRITABLE);
            $currentResult = Command::FAILURE;
        }
        if (!file_exists($this->arch) && $currentResult == Command::SUCCESS) {
            mkdir($this->arch, 0550);
        }
    }

    private function verifyKey(OutputInterface $output, int &$currentResult): void
    {
        if (!file_exists($this->keyParent)) {
            $output->writeln(BuildTreeCommand::BEGIN_ERROR_FOLDER . $this->keyParent . BuildTreeCommand::END_ERROR_NOT_FOUND);
            $currentResult = Command::FAILURE;
        }
        if (!is_writable($this->keyParent)) {
            $output->writeln(BuildTreeCommand::BEGIN_ERROR_FOLDER . $this->keyParent . BuildTreeCommand::END_ERROR_NOT_WRITABLE);
            $currentResult = Command::FAILURE;
        }
        if (!file_exists($this->key) && $currentResult == Command::SUCCESS) {
            mkdir($this->key, 0550);
        }
    }
}
