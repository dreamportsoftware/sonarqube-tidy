<?php

namespace SonarqubeTidy;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class SonarqubeTidyCommand extends Command
{
    /**
     * @var Bamboo
     */
    private $bamboo;

    /**
     * @var Sonarqube
     */
    private $sonarqube;

    /**
     * Setup the command
     */
    protected function configure()
    {
        $this
            ->setName('run')
            ->setDescription('Run a branch purge in Sonarqube')
            ->setDefinition(
                new InputDefinition(
                    [
                        new InputOption('username', 'u', InputOption::VALUE_OPTIONAL),
                        new InputOption('password', 'p', InputOption::VALUE_OPTIONAL),
                    ]
                )
            );

        // Create classes to interact with REST APIs
        $this->bamboo = new Bamboo();
        $this->sonarqube = new Sonarqube();
    }

    /**
     * Execute the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->authenticate($input, $output);

        // Get all projects in Sonarqube.
        $output->writeln('Fetching projects from Sonarqube.'.PHP_EOL);
        $projects = $this->sonarqube->getProjects();
        $canDelete = [];

        $output->writeln('Checking branches exist in Bamboo.'.PHP_EOL.PHP_EOL);
        $progress = new ProgressBar($output, count($projects));
        $progress->setFormat(
            "<info>%current%/%max%</info>[%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s%\n<fg=white;bg=blue>%message%</>\n"
        );

        foreach ($projects as $project) {
            // Check to see if they have active bamboo branches.
            $progress->setMessage($project['k']);
            $progress->advance();
            if ($this->bamboo->checkBranch($project['k'])) {
                $canDelete[] = $project['k'];
            }
            // throttle the requests slightly. Bamboo API gets a bit upset going full force.
            sleep(0.1);
        }
        $progress->finish();

        if (empty($canDelete)) {
            $output->writeln('Nothing to delete at this time.');

            return;
        }

        $output->writeln("Deleting branches from Sonarqube.".PHP_EOL.PHP_EOL);
        $progress = new ProgressBar($output, count($canDelete));
        $progress->setFormat(
            "<info>%current%/%max%</info>[%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s%\n<fg=white;bg=blue>%message%</>\n"
        );
        // Delete the ones that don't have active bamboo branches.
        foreach ($canDelete as $deleteBranch) {
            $progress->setMessage('Deleting '.$deleteBranch);
            $progress->advance();
            $this->sonarqube->deleteProject($deleteBranch);
        }
        $progress->finish();

        $output->writeln('Finished.');
    }

    /**
     * Gather authentication from the user and set it against each of the tools
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    private function authenticate(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        $username = $input->getOption('username');
        if (empty($username)) {
            $question = new Question('<question>What is your username?</question>');
            $question->setValidator(
                function ($value) {
                    if (empty($value)) {
                        throw new Exception('The username can not be empty.');
                    }
                    return $value;
                }
            );
            $question->setMaxAttempts(2);
            $username = $helper->ask($input, $output, $question);
        }

        $password = $input->getOption('password');

        if (!empty($password)) {
            $output->writeln("<error>Using a password on the command-line is insecure.</error>");
        }

        if (empty($password)) {
            $question = new Question('<question>What is your password?</question>');
            $question->setHidden(true);
            $question->setValidator(
                function ($value) {
                    if (empty($value)) {
                        throw new Exception('The password can not be empty.');
                    }
                    return $value;
                }
            );
            $question->setMaxAttempts(2);
            $password = $helper->ask($input, $output, $question);
        }

        // Pass authentication through to objects to make REST calls.
        $this->sonarqube->setUsername($username)->setPassword($password);
        $this->bamboo->setUsername($username)->setPassword($password);
    }
}
