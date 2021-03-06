<?php
/**
 * This file is part of the GitElephant package.
 *
 * (c) Matteo Giachino <matteog@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package GitElephant\Objects
 *
 * Just for fun...
 */

namespace GitElephant\Objects;


/**
 * An object representing a node in the tree
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

use GitElephant\Command\LsTreeCommand;
use GitElephant\Repository;

/**
 * A TreeObject instance represents a node in the git tree repository
 * It could be a file or a folder, as well as a submodule (a "link" talking the git language")
 */
class TreeObject
{
    const TYPE_BLOB = 'blob';
    const TYPE_TREE = 'tree';
    const TYPE_LINK = 'commit';

    /**
     * permissions
     *
     * @var string
     */
    private $permissions;

    /**
     * type
     *
     * @var string
     */
    private $type;

    /**
     * sha
     *
     * @var string
     */
    private $sha;

    /**
     * size
     *
     * @var string
     */
    private $size;

    /**
     * name
     *
     * @var string
     */
    private $name;

    /**
     * path
     *
     * @var string
     */
    private $path;

    /**
     * create a TreeObject from a single outputLine of the git ls-tree command
     *
     * @param string $outputLine output from ls-tree command
     *
     * @see LsTreeCommand::tree
     * @return TreeObject
     */
    public static function createFromOutputLine($outputLine)
    {
        $slices = static::getLineSlices($outputLine);
        $fullPath = $slices['fullPath'];
        if (false === $pos = mb_strrpos($fullPath, '/')) {
            // repository root
            $path = '';
            $name = $fullPath;
        } else {
            $path = substr($fullPath, 0, $pos);
            $name = substr($fullPath, $pos + 1);
        }

        return new self($slices['permissions'], $slices['type'], $slices['sha'], $slices['size'], $name, $path);
    }

    /**
     * Take a line and turn it in slices
     *
     * @param string $line a single line output from the git binary
     *
     * @return array
     */
    public static function getLineSlices($line)
    {
        preg_match('/^(\d+) (\w+) ([a-z0-9]+) +(\d+|-)\t(.*)$/', $line, $matches);
        $permissions = $matches[1];
        $type        = null;
        switch ($matches[2]) {
            case TreeObject::TYPE_TREE:
                $type = TreeObject::TYPE_TREE;
                break;
            case TreeObject::TYPE_BLOB:
                $type = TreeObject::TYPE_BLOB;
                break;
            case TreeObject::TYPE_LINK:
                $type = TreeObject::TYPE_LINK;
                break;
        }
        $sha      = $matches[3];
        $size     = $matches[4];
        $fullPath = $matches[5];

        return array(
            'permissions' => $permissions,
            'type'        => $type,
            'sha'         => $sha,
            'size'        => $size,
            'fullPath'    => $fullPath
        );
    }

    /**
     * Class constructor
     *
     * @param string $permissions node permissions
     * @param string $type        node type
     * @param string $sha         node sha
     * @param string $size        node size in bytes
     * @param string $name        node name
     * @param string $path        node path
     */
    public function __construct($permissions, $type, $sha, $size, $name, $path)
    {
        $this->permissions = $permissions;
        $this->type        = $type;
        $this->sha         = $sha;
        $this->size        = $size;
        $this->name        = $name;
        $this->path        = $path;
    }

    /**
     * toString magic method
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->name;
    }

    /**
     * Mime Type getter
     *
     * @param string $basePath the base path of the repository
     *
     * @return string
     */
    public function getMimeType($basePath)
    {
        return mime_content_type($basePath . DIRECTORY_SEPARATOR . $this->path);
    }

    /**
     * get extension if it's a blob
     *
     * @return string|null
     */
    public function getExtension()
    {
        $pos = strrpos($this->name, '.');
        if ($pos == false) {
            return null;
        } else {
            return substr($this->name, $pos+1);
        }
    }

    /**
     * whether the node is a tree
     *
     * @return bool
     */
    public function isTree()
    {
        return self::TYPE_TREE == $this->getType();
    }

    /**
     * whether the node is a link
     *
     * @return bool
     */
    public function isLink()
    {
        return self::TYPE_LINK == $this->getType();
    }

    /**
     * Full path getter
     *
     * @return string
     */
    public function getFullPath()
    {
        return rtrim('' == $this->path ? $this->name : $this->path.'/'.$this->name, '/');
    }

    /**
     * permissions getter
     *
     * @return string
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * sha getter
     *
     * @return string
     */
    public function getSha()
    {
        return $this->sha;
    }

    /**
     * type getter
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * name getter
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * path getter
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * size getter
     *
     * @return string
     */
    public function getSize()
    {
        return $this->size;
    }
}
