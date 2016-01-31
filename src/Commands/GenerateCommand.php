<?php

namespace Rainbow\Commands;

use Ramsey\Uuid\Uuid;
use Twig_Environment;
use Twig_Loader_Filesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class GenerateCommand
 *
 * @package \Rainbow\Commands
 */
class GenerateCommand extends Command
{
    /**
     * Themes collection.
     *
     * @var array
     */
    protected $themes = [];

    /**
     * Patterns collection.
     *
     * @var array
     */
    protected $patterns = [];

    /**
     * Progress bar instance.
     *
     * @var \Symfony\Component\Console\Helper\ProgressBar
     */
    protected $progress;

    /**
     * Set the command name and description.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('generate')
            ->setDescription('Generate all colour schemes.');
    }

    /**
     * Execute the generator command.
     *
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->themes = $this->loadThemes();
        $this->patterns = $this->loadPatterns();

        $this->setupProgressBar($output);

        $twig = new Twig_Environment(new Twig_Loader_Filesystem(BASE_PATH . '/resources/files'));

        foreach ($this->themes as $theme) {
            $theme->year = date('Y');
            $theme->uuid = Uuid::uuid4()->toString();

            foreach ($this->patterns as $pattern) {
                $this->progress->setMessage("Generating '{$theme->theme->name}' theme for '{$pattern->name}'.");

                foreach ($pattern->files as $source => $destination) {
                    $result = $twig->render($source, (array) $theme);
                    $destination = str_replace('{{theme}}', $theme->theme->slug, $destination);
                    if (count($pattern->files) > 1) {
                        $output = BASE_PATH . "/output/{$pattern->slug}/{$theme->theme->slug}/{$destination}";
                    } else {
                        $output = BASE_PATH . "/output/{$pattern->slug}/{$destination}";
                    }
                    @mkdir(dirname($output), 0777, true);
                    file_put_contents($output, $result);
                }
                $this->progress->advance();
            }
        }

        $this->progress->setMessage("Enjoy the themes!");
        $this->progress->finish();
    }

    /**
     * Load the themes connection.
     *
     * @return array
     */
    protected function loadThemes()
    {
        $themes = [];
        foreach (glob(BASE_PATH . '/resources/themes/*.json') as $path) {
            $themes[] = json_decode(file_get_contents($path));
        }

        return $themes;
    }

    /**
     * Load the patterns collection.
     *
     * @return array
     */
    protected function loadPatterns()
    {
        $patterns = [];
        foreach (glob(BASE_PATH . '/resources/patterns/*.json') as $path) {
            $patterns[] = json_decode(file_get_contents($path));
        }

        return $patterns;
    }

    /**
     * Setup the progress bar.
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function setupProgressBar(OutputInterface $output)
    {
        $this->progress = new ProgressBar($output, count($this->themes) * count($this->patterns));
        $this->progress->setFormat("<info>%message%</info>\n<fg=red>[</>%bar%<fg=red>]</> <fg=yellow>(%current%/%max%) (%elapsed%)</>");
        $this->progress->setBarCharacter('<fg=blue>#</>');
        $this->progress->setProgressCharacter("<fg=magenta>#</>");
        $this->progress->setMessage('Test');
        $this->progress->start();
    }
}
