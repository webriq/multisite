<?php

namespace Grid\MultisiteCentral\Model\Site;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use Zork\Model\Structure\MapperAwareAbstract;

/**
 * Structure
 *
 * @author David Pozsar <david.pozsar@megaweb.hu>
 */
class Structure extends MapperAwareAbstract
{

    /**
     * Field: id
     *
     * @var int
     */
    protected $id;

    /**
     * Field: schema
     *
     * @var string
     */
    protected $schema;

    /**
     * Field: ownerId
     *
     * @var int
     */
    protected $ownerId;

    /**
     * Field: created
     *
     * @var string
     */
    protected $created;

    /**
     * Field: domains
     *
     * @var array
     */
    private $domains;

    /**
     * @return string
     */
    public function getDomains()
    {
        return $this->domains;
    }

    /**
     * @param   array|string|null   $domains
     * @return  \MultisiteCentral\Model\Site\Structure
     */
    public function setDomains( $domains )
    {
        if ( $domains === null )
        {
            $domains = array();
        }
        else if ( ! is_array( $domains ) )
        {
            $domains = array_map( 'trim', explode( "\n", $domains ) );
        }

        $this->domains = $domains;
        return $this;
    }

    /**
     * Remove a directory
     *
     * @param   string  $dir
     * @return  void
     */
    protected function removeDir( $dir )
    {
        if ( ! is_dir( $dir ) )
        {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $dir,
                RecursiveDirectoryIterator::CURRENT_AS_FILEINFO |
                RecursiveDirectoryIterator::KEY_AS_PATHNAME |
                RecursiveDirectoryIterator::SKIP_DOTS |
                RecursiveDirectoryIterator::UNIX_PATHS
            ),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ( $iterator as $pathname => $fileinfo )
        {
            /* @var $fileinfo \SplFileInfo */

            if ( $fileinfo->isDir() )
            {
                @ rmdir( $pathname );
            }
            else
            {
                @ unlink( $pathname );
            }
        }
    }

    /**
     * Delete should remove uploads directory too
     *
     * @return int
     */
    public function delete()
    {
        static $publicDirs = array( 'uploads', 'thumbnails' );
        $success = parent::delete();

        if ( $success )
        {
            foreach ( $publicDirs as $publicDir )
            {
                $this->removeDir( implode( DIRECTORY_SEPARATOR, array(
                    '.',
                    'public',
                    $publicDir,
                    $this->schema
                ) ) );
            }
        }

        return $success;
    }

}
