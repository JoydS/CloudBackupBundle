<?php
namespace Dizda\CloudBackupBundle\Databases;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;


abstract class BaseDatabase
{
    const DB_PATH = '';

    protected $kernelCacheDir;
    protected $filesystem;
    protected $basePath;
    protected $dataPath;
    protected $archivePath;



    public function __construct()
    {
        $this->filesystem = new Filesystem();
    }


    /**
     * Preparation of directory
     *
     * $this->basePath      /Users/high/Sites/dizdabundles/app/cache/dev/db/
     * $this->dataPath      /Users/high/Sites/dizdabundles/app/cache/dev/db/mongo/
     * $this->archivePath   /Users/high/Sites/dizdabundles/app/cache/dev/db/bambou_2013_01_12-01_36_33.tar
     *
     * TODO: Add a config prefix to archive (with default value : '')
     * TODO: Many compression mode
     */
    final public function prepare()
    {
        $this->basePath     = $this->kernelCacheDir . '/db/';
        $this->dataPath     = $this->basePath . static::DB_PATH . '/';

        $this->filesystem->mkdir($this->dataPath);
    }


    /**
     * Compress with format name like : hostname_2013_01_12-00_06_40.tar
     */
    final public function compression()
    {
        $fileName           = gethostname() . '_' . date('Y_m_d-H_i_s') . '.tar';
        $this->archivePath  = $this->basePath . $fileName;


        $archive = sprintf('tar --exclude=%s -czf %s -C %s . 2>/dev/null',
                            $fileName,  // Yo dawg, I heard you don't like so much tar archive, so I don't make tar in a tar archive, you cannot extracting while you extract, damn!
                            $this->archivePath,
                            $this->basePath);

        $this->execute($archive);
    }


    /**
     * Handle process error on fails
     *
     * @param $command
     * @throws \RuntimeException
     */
    protected function execute($command)
    {
        $process = new Process($command);


        $process->run(function ($type, $data) {
            if (Process::ERR == $type) {
                throw new \RuntimeException($data);
            }
        });
    }


    /**
     * Remove all dirs with files
     *
     */
    final public function cleanUp()
    {
        $this->filesystem->remove($this->basePath);
    }


    /**
     * Migration procedure for each databases type
     *
     * @return mixed
     */
    abstract public function dump();

    /**
     * Return path of the archive
     *
     * @return mixed
     */
    public function getArchivePath()
    {
        return $this->archivePath;
    }


    /**
     * @DI\InjectParams({
     *     "kernelCacheDir" = @DI\Inject("%kernel.cache_dir%")
     * })
     */
    public function setKernelCacheDir($kernelCacheDir)
    {
        $this->kernelCacheDir = $kernelCacheDir;
    }

}