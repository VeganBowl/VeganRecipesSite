<?php

namespace Command;

use Cilex\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class BuildCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('build')
            ->setDescription('Build the site')
            // ->addArgument('name', InputArgument::OPTIONAL, 'Qui voulez vous saluer??')
            // ->addOption('yell', null, InputOption::VALUE_NONE, 'Si définie, la tâche criera en majuscules')
        ;
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container  = $this->getApplication()->getContainer();
        $fs         = new Filesystem();
        $finder     = new Finder();
        $parser     = $container['article_parser'];
        $recipes    = [];

        // Initialize build directory
        $fs->mkdir($container['build_dir']);

        // Find recipes
        $finder->directories()->in($container['recipes_dir']);
        foreach ($finder as $recipeDir) {
            $recipePath         = $recipeDir->getRealpath();
            $recipeGenericName  = $recipeDir->getRelativePathname();

            // Workaround since $finder->directories()->in(dir) return also the dir itself
            if ($recipePath == realpath($container['recipes_dir'])) {
                continue;
            }

            $recipes[$recipeGenericName] = [];

            $recipeFinder = new Finder();
            $recipeFinder->files()->in($recipePath)->name('index.*.md');

            foreach ($recipeFinder as $recipeFile) {
                $fileName = pathinfo($recipeFile->getRealpath())['basename'];

                if (preg_match('#index.([a-z]+).md#', $fileName, $match)) {
                    $lang = $match[1];
                    $recipes[$recipeGenericName][$lang] = $recipeFile;
                }
            }

            $mainPicture = null;
            if ($fs->exists($recipePath.'/main.jpg')) {
                $fs->copy($recipePath.'/main.jpg', $container['build_dir'].'/'.$recipeDir->getRelativePathname().'/main.jpg');
                $mainPicture = 'main.jpg';
            }

            // Generate recipe in all languages
            foreach ($recipes[$recipeGenericName] as $lang => $recipeFile) {
                    $content = $recipeFile->getContents();

                    $langs = array_keys($recipes[$recipeGenericName]);
                    unset($langs[array_search($lang, $langs)]);

                    $data = $parser->parse($content);
                    $html = $container['twig']->render('recipe.html.twig', [
                        'content'       => $data['content'],
                        'meta'          => $data['frontMatter'],
                        'langs'         => $langs,
                        'mainPicture'   => $mainPicture,
                    ]);

                    $fs->mkdir($container['build_dir'].'/'.$recipeDir->getRelativePathname(), 0777);
                    $fs->dumpFile($container['build_dir'].'/'.$recipeDir->getRelativePathname().'/index.'.$lang.'.html', $html);
            }

//            if (!$fs->exists($recipePath.'/index.en.md')) {
//                $output->writeln(
//                    sprintf('ERROR : recipe not found in %s', $recipePath)
//                );
//                die();
//            }
        }

        $output->writeln('Page generation done');
    }
}
