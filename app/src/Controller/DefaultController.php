<?php declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function indexAction(Request $request): Response
    {
        $user = $this->getUser();
        $directoryList = $fileList = [];
        $currentDir = $parentDir = '';
        if ($user) {

            list($currentDir, $parentDir, $absoluteDir) = $this->sanitizePath(
                sprintf("%s/files", $this->getParameter('kernel.project_dir')),
                $request->query->get('dir', '')
            );

            try {
                $finder = (new Finder())
                    ->ignoreUnreadableDirs()
                    ->in($absoluteDir)
                    ->sortByName()
                    ->depth('== 0');

                foreach ($finder->directories() as $dir) {
                    $directoryList[] = $dir->getRelativePathname();
                }
                foreach ($finder->files() as $file) {
                    $fileList[] = $file->getFilename();
                }

            } catch (DirectoryNotFoundException $e) {
                throw $this->createNotFoundException();
            }

        }
        return $this->render('index.html.twig', [
            'directoryList' => $directoryList,
            'fileList' => $fileList,
            'currentDir' => $currentDir,
            'parentDir' => $parentDir,
        ]);
    }

    /**
     * @Route("/file", name="file", )
     */
    public function fileAction(Request $request): Response|NotFoundHttpException
    {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createNotFoundException();
        }

        list($relativePath, $parentDir, $absolutePath) = $this->sanitizePath(
            sprintf("%s/files",$this->getParameter('kernel.project_dir')),
            $request->query->get('path', '/')
        );

        if (!file_exists($absolutePath)){
            throw $this->createNotFoundException();
        }

        $file = new \SplFileInfo($absolutePath);

        return $this->file($file, 'foo', ResponseHeaderBag::DISPOSITION_INLINE);
    }

    /* @TODO: move to some helper */
    private function sanitizePath(string $baseDir, string $path): array
    {
        $parentDir = '';
        $absoluteDir = realpath($baseDir.$path);
        if (!$absoluteDir || !str_starts_with($absoluteDir, $baseDir)) {
            $absoluteDir = $baseDir;
        }
        if (str_replace($baseDir, '', $absoluteDir) !== '') {
            $parentDir = str_replace($baseDir, '', realpath($absoluteDir.'/../'));
        }
        $path = str_replace($baseDir, '', $absoluteDir);

        dump($absoluteDir);
        dump($parentDir);
        dump($path);
        return [$path, $parentDir, $absoluteDir];
    }
}
