<?php
/**
 * @copyright Copyright (c) 2013-2017 2amigos! Consulting Group LLC
 * @link      http://2amigos.us
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

namespace common\components\arrayQuery;

use common\components\arrayQuery\conditions\{Equal,
    GreaterThan,
    GreaterThanOrEqual,
    LessThan,
    LessThanOrEqual,
    Like,
    NotLike};
use yii\base\Arrayable;
use yii\base\BaseObject;

/**
 * ArrayQuery allows to filter an array by apply conditions.
 *
 * @package common\components\arrayQuery
 */
class ArrayQuery
{
    /**
     * The data to search, filter.
     */
    private array $data;
    /**
     * The array tokenized so user can search multidimensional array by key paths -ie `parentkey.child`
     */
    private array $tokens;
    /**
     * The conditions to apply
     */
    private array $conditions = [];

    public function __construct(array $array)
    {
        $this->data = $array;
        foreach ($array as $k => $item) {
            $this->tokens[$k] = $this->tokenize($item, '', false);
        }
    }

    /**
     * Adds a condition to apply the array
     *
     * @param string $key      the key to search in the array
     * @param mixed  $value    the value to search. It supports SQL like operator plus some custom ones:
     *                         - `~` or `like` : like `%value%` in SQL
     *                         - `n~` or `nlike` : like `NOT LIKE` in SQL
     * @param string $operator the operator. It can be `and` or `or`. If any of `or` matches it will be added to the
     *                         successful results.
     */
    public function addCondition(string $key, mixed $value, string $operator = 'and'): static
    {
        if ($value !== null) { // not accepting null values
            $operation = null;
            $operator = strcasecmp($operator, 'or') !== 0 ? 'and' : 'or';

            if (preg_match('/^(?:\s*(<>|<=|>=|<|>|=|~|n~|like|nlike))?(.*)$/i', $value, $matches)) {
                $operation = $matches[1];
                $value = trim($matches[2]);
            }

            if (empty($operation) || strlen($operation) > 5) {
                $operation = '=';
            }

            switch ($operation) {
                case '<':
                    $condition = new LessThan($value);
                    break;
                case '>':
                    $condition = new GreaterThan($value);
                    break;
                case '<>':
                    $condition = new Equal($value);
                    $condition->reverse();
                    break;
                case '<=':
                    $condition = new LessThanOrEqual($value);
                    break;
                case '>=':
                    $condition = new GreaterThanOrEqual($value);
                    break;
                case '~':
                case 'like':
                    $condition = new Like($value);
                    break;
                case 'n~':
                case 'nlike':
                    $condition = new NotLike($value);
                    break;
                case '=':
                default:
                    $condition = new Equal($value);
            }
            $this->conditions[$operator][] = ['condition' => $condition, 'key' => $key];
        }
        return $this;
    }

    /**
     * Returns the first matched result.
     *
     * @return array the first matched result, empty array if none found.
     */
    public function one(): array
    {
        foreach ($this->tokens as $key => $token) {
            if (!$this->matches($token)) {
                continue;
            }
            return $this->data[$key];
        }
        return [];
    }

    /**
     * Returns array of matched results.
     *
     * @return array the matched results.
     */
    public function find(): array
    {
        if (empty($this->conditions)) {
            return $this->data;
        }
        $results = [];
        foreach ($this->tokens as $key => $token) {
            if (!$this->matches($token)) {
                continue;
            }
            $results[$key] = $this->data[$key];
        }
        return $results;
    }

    /**
     * Tokenizes the array to ease the search in multidimensional arrays.
     *
     * @param array|BaseObject $array     $array     the array to tokenize
     * @param string           $prefix    the key prefix
     * @param bool             $addParent whether to add parent value anyway. False
     */
    public function tokenize(array|BaseObject $array, string $prefix = '', bool $addParent = true): array
    {
        $paths = [];
        $px = empty($prefix) ? null : $prefix . ".";
        foreach ($array as $key => $items) {
            if (is_array($items)) {
                $addParent && $paths[$px . $key] = $items;
                foreach ($this->tokenize($items, $px . $key) as $k => $path) {
                    $paths[$k] = $path;
                }
            } elseif (is_object($items)) {
                $addParent && $paths[$px . $key] = $items;
                foreach ($this->tokenize(get_object_vars($items), $px . $key) as $k => $path) {
                    $paths[$k] = $path;
                }
            } else {
                $paths[$px . $key] = $items;
            }
        }
        return $paths;
    }

    /**
     * Checks data against conditions
     *
     * @param array|Arrayable $data the data to match against.
     *
     * @return bool true if matches condition
     */
    private function matches(array|Arrayable $data): bool
    {
        $matches = true;
        $conditions = $this->conditions['and'] ?? [];
        foreach ($conditions as $condition) {
            $key = $condition['key'];
            $condition = $condition['condition'];
            if (!array_key_exists($key, $data) || !$condition->matches($data[$key])) {
                $matches = false;
                break;
            }
        }
        $conditions = $this->conditions['or'] ?? [];
        foreach ($conditions as $condition) {
            $key = $condition['key'];
            $condition = $condition['condition'];
            if (array_key_exists($key, $data) && $condition->matches($data[$key])) {
                $matches = true;
                break;
            }
        }
        return $matches;
    }
}
