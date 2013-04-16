<?php

namespace Grid\MultisiteCentral\Model\Git;

use Zend\Paginator\Paginator;
use Zork\Model\Mapper\ReadOnlyMapperInterface;
use Zork\Model\Mapper\ReadListMapperInterface;

/**
 * Mapper
 *
 * @author David Pozsar <david.pozsar@megaweb.hu>
 */
class Mapper implements ReadOnlyMapperInterface,
                        ReadListMapperInterface
{

    /**
     * Structure prototype for the mapper
     *
     * @var \MultisiteCentral\Model\Git\Structure
     */
    protected $structurePrototype;

    /**
     * Get structure prototype
     *
     * @return \MultisiteCentral\Model\Git\Structure
     */
    public function getStructurePrototype()
    {
        return $this->structurePrototype;
    }

    /**
     * Set structure prototype
     *
     * @param \MultisiteCentral\Model\Git\Structure $structurePrototype
     * @return \MultisiteCentral\Model\Git\Mapper
     */
    public function setStructurePrototype( $structurePrototype )
    {
        if ( $structurePrototype instanceof MapperAwareInterface )
        {
            $structurePrototype->setMapper( $this );
        }

        $this->structurePrototype = $structurePrototype;
        return $this;
    }

    /**
     * Create structure from plain data
     *
     * @param array $data
     * @return \MultisiteCentral\Model\Git\Structure
     */
    protected function createStructure( array $data )
    {
        $structure = clone $this->structurePrototype;
        $structure->setOptions( $data );

        if ( $structure instanceof MapperAwareInterface )
        {
            $structure->setMapper( $this );
        }

        return $structure;
    }

    /**
     * Constructor
     *
     * @param \MultisiteCentral\Model\Git\Structure $structurePrototype
     */
    public function __construct( Structure $structurePrototype = null )
    {
        $this->setStructurePrototype( $structurePrototype ?: new Structure );
    }

    /**
     * Exec a git command
     *
     * @param  array $params
     * @return array
     * @throws \RuntimeException
     */
    protected function command( array $params )
    {
        $result = 0;
        $lines  = array();

        if ( escapeshellarg( '%' ) == ' ' )
        {
            $params = array_map( 'escapeshellarg', array_values( $params ) );
        }

        array_unshift( $params, 'git' );
        $exec = implode( ' ', $params );

        @ exec( $exec, $lines, $result );

        if ( $result )
        {
            throw new \RuntimeException( sprintf(
                '`%s` returned code: %s',
                $exec,
                $result
            ) );
        }

        return (array) $lines;
    }

    /**
     * Find a structure
     *
     * @param int|array $nth
     * @return \MultisiteCentral\Model\Git\Structure
     */
    public function find( $nth )
    {
        if ( is_array( $nth ) )
        {
            $nth = reset( $nth );
        }

        return $this->findOne( array(
            'skip' => (int) $nth,
        ) );
    }

    /**
     * Find one structure
     *
     * @param array|null $where
     * @param bool|null $isReversed
     * @return \MultisiteCentral\Model\Git\Structure
     */
    public function findOne( $where = null, $isReversed = null )
    {
        foreach ( $this->findAll( $where, $isReversed, 1, 0 ) as $structure )
        {
            return $structure;
        }
    }

    /**
     * Find multiple structures
     *
     * @param array|null $where
     * @param bool|null $isReversed
     * @param int|null $limit
     * @param int|null $offset
     * @return \MultisiteCentral\Model\Git\Structure[]
     */
    public function findAll( $where         = null,
                             $isReversed    = null,
                             $limit         = null,
                             $offset        = null )
    {
        $command    = array( 'log' );
        $where      = (array) $where;

        if ( null !== $limit )
        {
            $where['max-count'] = (int) $limit;
        }

        if ( null !== $offset )
        {
            $where['skip'] = (int) $offset;
        }

        if ( true === $isReversed )
        {
            $where['first-parent'] = true;
        }

        if ( empty( $where['format'] ) )
        {
            $where['format'] = array(
                'commitHash'        => '%H',
                'commitAbbr'        => '%h',
                'treeHash'          => '%T',
                'treeAbbr'          => '%t',
                'authorName'        => '%an',
                'authorEmail'       => '%ae',
                'committerName'     => '%cn',
                'committerEmail'    => '%ce',
                'committed'         => '%ci',
                'subject'           => '%s',
            );
        }

        if ( is_array( $where['format'] ) )
        {
            $format = '';

            foreach ( $where['format'] as $field => $frmt )
            {
                $format .= '%x01' . $field . '%x02' . $frmt . '%x03';
            }

            $where['format'] = $format;
        }

        foreach ( $where as $key => $value )
        {
            if ( null !== $value )
            {
                if ( is_bool( $value ) )
                {
                    if ( $value )
                    {
                        $command[] = '--' . $key;
                    }
                }
                else
                {
                    $command[] = '--' . $key . '=' . $value;
                }
            }
        }

        $result = array();

        foreach ( $this->command( $command ) as $line )
        {
            $params = array();
            $parts  = explode( chr( 1 ), $line );

            while ( ! empty( $parts ) )
            {
                @ list( $key, $value ) = explode( chr( 2 ), array_shift( $parts ) );

                if ( ! empty( $key ) )
                {
                    list( $value )   = explode( chr( 3 ), $value );
                    $params[$key]    = $value;
                }
            }

            $result[] = $this->createStructure( $params );
        }

        return $result;
    }

    /**
     * Get paginator
     *
     * @param array|null $where
     * @param bool|null $isReversed
     * @return \Zend\Paginator\Paginator
     */
    public function getPaginator( $where        = null,
                                  $isReversed   = null )
    {
        return new Paginator( new PaginatorAdapter( $this, $where, $isReversed ) );
    }

}
