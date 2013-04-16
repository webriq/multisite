<?php

namespace Grid\MultisiteCentral\Model\Site;

use Zend\Db\Sql;
use Zork\Db\Sql\Expression\FunctionCall;
use Zork\Model\Mapper\DbAware\ReadWriteMapperAbstract;

/**
 * Mapper
 *
 * @author David Pozsar <david.pozsar@megaweb.hu>
 */
class Mapper extends ReadWriteMapperAbstract
{

    /**
     * Schema used in all queries
     *
     * @var string
     */
    protected $dbSchema = '_central';

    /**
     * Table name used in all queries
     *
     * @var string
     */
    protected static $tableName = 'site';

    /**
     * Domain-table name used in all queries
     *
     * @var string
     */
    protected static $domainTableName = 'domain';

    /**
     * Default column-conversion functions as types;
     * used in selected(), deselect()
     *
     * @var array
     */
    protected static $columns = array(
        'id'        => self::INT,
        'schema'    => self::STR,
        'ownerId'   => self::INT,
        'created'   => self::DATETIME,
    );

    /**
     * Use sql's "DEFAULT" keyword on
     * save if value is NULL
     *
     * @var array
     */
    protected static $useDefaultOnSave = array(
        'created'   => true,
    );

    /**
     * Contructor
     *
     * @param \MultisiteCentral\Model\Site\Structure $multisiteCentralSiteStructurePrototype
     */
    public function __construct( Structure $multisiteCentralSiteStructurePrototype = null )
    {
        parent::__construct( $multisiteCentralSiteStructurePrototype ?: new Structure );
    }

    /**
     * Get select() default columns
     *
     * @return array
     */
    protected function getSelectColumns( $columns = null )
    {
        if ( null === $columns )
        {
            $domains = true;
        }
        elseif ( ( $index = array_search( 'domains', $columns ) ) )
        {
            $domains = true;
            unset( $columns[$index] );
        }
        else
        {
            $domains = false;
        }

        $columns  = parent::getSelectColumns( $columns );
        $platform = $this->getDbAdapter()
                         ->getPlatform();

        if ( $domains )
        {
            $columns['domains'] = new Sql\Expression( '(' .
                $this->sql( $this->getTableInSchema(
                        static::$domainTableName
                     ) )
                     ->select()
                     ->columns( array( new FunctionCall(
                         'string_agg',
                         array( 'domain', "\n" ),
                         array( FunctionCall::TYPE_IDENTIFIER,
                                FunctionCall::TYPE_VALUE )
                     ) ) )
                     ->where( array(
                         new Sql\Predicate\Expression(
                             $platform->quoteIdentifierChain( array(
                                 static::$domainTableName, 'siteId'
                             ) ) .
                             ' = ' .
                             $platform->quoteIdentifierChain( array(
                                 static::$tableName, 'id'
                             ) )
                         ),
                     ) )
                     ->getSqlString( $platform ) .
            ')' );
        }

        return $columns;
    }

}
