<?php

namespace Command;

use Cilex\Command\Command;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
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
        $allLangs   = [];

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

                if (preg_match('#index\.([a-z]+)\.md#', $fileName, $match)) {
                    $lang = $match[1];
                    $recipes[$recipeGenericName][$lang] = [
                        'file'  => $recipeFile,
                        'name'  => $recipeDir->getRelativePathname(),
                    ];
                }
            }

            // Generate recipe in all languages
            foreach ($recipes[$recipeGenericName] as $lang => $recipeFile) {
                $allLangs[] = $lang;

                $mainPicture = null;
                if ($fs->exists($recipePath.'/main.jpg')) {

                    $imagine = new Imagine();
                    $image = $imagine->open($recipePath.'/main.jpg');
                    $thumbnail = $image->thumbnail(new Box(900, 600), ImageInterface::THUMBNAIL_OUTBOUND);
                    $fs->mkdir($container['build_dir'].'/'.$lang.'/'.$recipeDir->getRelativePathname());
                    $thumbnail->save($container['build_dir'].'/'.$lang.'/'.$recipeDir->getRelativePathname().'/main.jpg');

//                    $fs->copy($recipePath.'/main.jpg', $container['build_dir'].'/'.$lang.'/'.$recipeDir->getRelativePathname().'/main.jpg');
                    $mainPicture = 'main.jpg';

                    $recipes[$recipeGenericName][$lang]['mainPicture'] = $mainPicture;
                }

                $content = $recipeFile['file']->getContents();

                $langs = array_keys($recipes[$recipeGenericName]);
                unset($langs[array_search($lang, $langs)]);

                $data = $parser->parse($content);
                $html = $container['twig']->render('recipe.html.twig', [
                    'content'       => $data['content'],
                    'meta'          => $data['frontMatter'],
                    'langs'         => $langs,
                    'lang'          => $lang,
                    'recipes'       => $recipes[$recipeGenericName],
                    'mainPicture'   => $mainPicture,
                ]);

                $recipes[$recipeGenericName][$lang]['title'] = $data['frontMatter']['title'];

                $fs->mkdir($container['build_dir'].'/'.$lang.'/'.$recipeDir->getRelativePathname(), 0777);
                $fs->dumpFile($container['build_dir'].'/'.$lang.'/'.$recipeDir->getRelativePathname().'/index.html', $html);
            }

            $allLangs = array_unique($allLangs);

            // Homes generation
            foreach ($allLangs as $lang) {
                $otherLanguages = $allLangs;
                unset($otherLanguages[array_search($lang, $otherLanguages)]);

                $recipesForThisLang = $recipes;
                foreach ($recipes as $recipeName => $recipe) {
                    if (!isset($recipe[$lang])) {
                        unset($recipesForThisLang[$recipeName]);
                    }
                }

                $fs->dumpFile($container['build_dir'].'/'.$lang.'/index.html', $container['twig']->render('index.html.twig', [
                    'recipes'   => $recipesForThisLang,
                    'lang'      => $lang,
                    'langs'     => $otherLanguages,
                ]));
            }

            // Translation pages generation
            foreach ($allLangs as $lang) {
                $fs->dumpFile($container['build_dir'].'/'.$lang.'/translation-state.html', $container['twig']->render('translationState.html.twig', [
                    'recipes'   => $recipes,
                    'lang'      => $lang,
                    'langs'     => $allLangs,
                ]));
            }
        }

        $output->writeln('Page generation done');
    }
}
