<?php

namespace Grid\MultisitePlatform\Model\Domain;

use Zend\Db\Sql\Predicate\Operator;
use Zend\Db\Sql\Expression as SqlExpression;
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
    protected static $tableName = 'domain';

    /**
     * Default column-conversion functions as types;
     * used in selected(), deselect()
     *
     * @var array
     */
    protected static $columns = array(
        'id'        => self::INT,
        'domain'    => self::STR,
        'siteId'    => self::INT,
    );

    /**
     * Contructor
     *
     * @param \MultisiteCentral\Model\Domain\Structure $multisiteCentralDomainStructurePrototype
     */
    public function __construct( Structure $multisiteCentralDomainStructurePrototype = null )
    {
        parent::__construct( $multisiteCentralDomainStructurePrototype ?: new Structure );
    }

    /**
     * Is domain already exists
     *
     * @param  string   $domain
     * @param  int      $excludeId
     * @return bool
     */
    public function isDomainExists( $domain, $excludeId = null )
    {
        return $this->isExists( empty( $excludeId ) ? array(
            'domain' => $domain,
        ): array(
            'domain' => $domain,
            new Operator( 'id', Operator::OP_NE, $excludeId )
        ) );
    }

    /**
     * Is a schema exists
     *
     * @param string $schema
     * @return int
     */
    public function isSchemaExists( $schema )
    {
        $sql    = $this->sql( $this->getTableInSchema( 'site' ) );
        $select = $sql->select()
                      ->columns( array(
                          'count' => new SqlExpression( 'COUNT(*)' ),
                      ) )
                      ->where( array(
                          'schema' => $schema,
                      ) );

        $result = $sql->prepareStatementForSqlObject( $select )
                      ->execute();

        $affected = $result->getAffectedRows();

        if ( $affected < 1 )
        // Just-in-case
        // @codeCoverageIgnoreStart
        {
            return null;
        }
        // @codeCoverageIgnoreEnd

        if ( $affected > 1 )
        {
            // Just-in-case
            // @codeCoverageIgnoreStart
            throw new \Zend\Db\Exception\UnexpectedValueException(
                'Too many rows'
            );
            // @codeCoverageIgnoreEnd
        }

        foreach ( $result as $row )
        {
            return (int) $row['count'];
        }
    }

}
