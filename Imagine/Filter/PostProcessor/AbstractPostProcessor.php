<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Imagine\Filter\PostProcessor;

use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Binary\FileBinaryInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

abstract class AbstractPostProcessor implements PostProcessorInterface
{
    /**
     * @var string
     */
    protected $executablePath;

    /**
     * @var string|null
     */
    protected $temporaryRootPath;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @param string      $executablePath
     * @param string|null $temporaryRootPath
     */
    public function __construct($executablePath, $temporaryRootPath = null)
    {
        $this->executablePath = $executablePath;
        $this->temporaryRootPath = $temporaryRootPath;
        $this->filesystem = new Filesystem();
    }

    /**
     * Performs post-process operation on passed binary and returns the resulting binary.
     *
     * @param BinaryInterface $binary
     * @param array           $options
     *
     * @throws ProcessFailedException
     *
     * @return BinaryInterface
     */
    public function process(BinaryInterface $binary, array $options = []): BinaryInterface
    {
        return $this->doProcess($binary, func_num_args() >= 2 ? func_get_arg(1) : []);
    }

    /**
     * @param BinaryInterface $binary
     * @param array           $options
     *
     * @throws ProcessFailedException
     *
     * @return BinaryInterface
     */
    abstract protected function doProcess(BinaryInterface $binary, array $options): BinaryInterface;

    /**
     * @param array $arguments
     * @param array $options
     *
     * @return Process
     */
    protected function createProcess(array $arguments = [], array $options = []): Process
    {
        $process = new Process($arguments);

        if (!isset($options['process'])) {
            return $process;
        }

        if (isset($options['process']['timeout'])) {
            $process->setTimeout($options['process']['timeout']);
        }

        if (isset($options['process']['working_directory'])) {
            $process->setWorkingDirectory($options['process']['working_directory']);
        }

        if (isset($options['process']['environment_variables']) && is_array($options['process']['environment_variables'])) {
            $process->setEnv($options['process']['environment_variables']);
        }

        return $process;
    }

    /**
     * @param BinaryInterface $binary
     *
     * @return bool
     */
    protected function isBinaryTypeJpgImage(BinaryInterface $binary): bool
    {
        return $this->isBinaryTypeMatch($binary, array('image/jpeg', 'image/jpg'));
    }

    /**
     * @param BinaryInterface $binary
     *
     * @return bool
     */
    protected function isBinaryTypePngImage(BinaryInterface $binary): bool
    {
        return $this->isBinaryTypeMatch($binary, array('image/png'));
    }

    /**
     * @param BinaryInterface $binary
     * @param string[]        $types
     *
     * @return bool
     */
    protected function isBinaryTypeMatch(BinaryInterface $binary, array $types): bool
    {
        return in_array($binary->getMimeType(), $types, true);
    }

    /**
     * @param BinaryInterface $binary
     * @param array           $options
     * @param null            $prefix
     *
     * @return string
     */
    protected function writeTemporaryFile(BinaryInterface $binary, array $options = array(), $prefix = null): string
    {
        $temporary = $this->acquireTemporaryFilePath($options, $prefix);

        if ($binary instanceof FileBinaryInterface) {
            $this->filesystem->copy($binary->getPath(), $temporary, true);
        } else {
            $this->filesystem->dumpFile($temporary, $binary->getContent());
        }

        return $temporary;
    }

    /**
     * @param array  $options
     * @param string $prefix
     *
     * @return string
     */
    protected function acquireTemporaryFilePath(array $options, $prefix = null): string
    {
        $root = $options['temp_dir'] ?? $this->temporaryRootPath ?: sys_get_temp_dir();

        if (!is_dir($root)) {
            try {
                $this->filesystem->mkdir($root);
            } catch (IOException $exception) {
                // ignore failure as "tempnam" function will revert back to system default tmp path as last resort
            }
        }

        if (false === $file = @tempnam($root, $prefix ?: 'post-processor')) {
            throw new \RuntimeException(sprintf('Temporary file cannot be created in "%s"', $root));
        }

        return $file;
    }

    /**
     * @param Process $process
     * @param array   $validReturns
     * @param array   $errorStrings
     *
     * @return bool
     */
    protected function isSuccessfulProcess(Process $process, array $validReturns = [0], array $errorStrings = ['ERROR']): bool
    {
        if (count($validReturns) > 0 && !in_array($process->getExitCode(), $validReturns)) {
            return false;
        }

        foreach ($errorStrings as $string) {
            if (false !== strpos($process->getOutput(), $string)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $method
     */
    protected function triggerSetterMethodDeprecation($method): void
    {
        @trigger_error(sprintf('The %s() method was deprecated in 1.10.0 and will be removed in 2.0. You must '
            .'setup the class state via its __construct() method. You can still pass filter-specific options to the '.
            'process() method to overwrite behavior.', $method), E_USER_DEPRECATED);
    }
}
