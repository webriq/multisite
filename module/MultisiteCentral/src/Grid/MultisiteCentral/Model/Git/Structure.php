<?php

namespace Grid\MultisiteCentral\Model\Git;

use Zork\Model\Structure\StructureAbstract;

/**
 * Structure
 *
 * @author David Pozsar <david.pozsar@megaweb.hu>
 */
class Structure extends StructureAbstract
{

    public $commitHash;

    public $commitAbbr;

    public $treeHash;

    public $treeAbbr;

    public $authorName;

    public $authorEmail;

    public $committerName;

    public $committerEmail;

    public $committed;

    public $subject;

}
