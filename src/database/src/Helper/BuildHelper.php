<?php

namespace Mix\Database\Helper;

use Mix\Database\Query\Expr;

/**
 * Class BuildHelper
 * @package Mix\Database\Helper
 * @author liu,jian <coder.keda@gmail.com>
 */
class BuildHelper
{

    /**
     * 是否为多个Where/On
     * @param $where
     * @return bool
     */
    public static function isMulti($where)
    {
        foreach ($where as $value) {
            if (!is_array($value)) {
                return false;
            }
            $length = count($value);
            if (!in_array($length, [2, 3])) {
                return false;
            }
        }
        return true;
    }

    /**
     * 构建数据
     * @param array $data
     * @return array
     */
    public static function data(array $data)
    {
        $sql    = [];
        $params = [];
        foreach ($data as $key => $item) {
            if (is_array($item)) {
                list($operator, $value) = $item;
                $sql[]        = "`{$key}` =  `{$key}` {$operator} :{$key}";
                $params[$key] = $value;
                continue;
            }
            $sql[]        = "`{$key}` = :{$key}";
            $params[$key] = $item;
        }
        return [implode(', ', $sql), $params];
    }

    /**
     * 构建条件
     * @param array $where
     * @param int $id
     * @return array
     */
    public static function where(array $where, &$id = null)
    {
        $sql    = '';
        $params = [];
        foreach ($where as $key => $item) {
            $id++;
            $prefix = "__{$id}_";
            $length = count($item);
            // php switch continue 非常奇葩，所以用 if else
            if ($length == 2) {
                // 子条件
                if (in_array($item[0], ['or', 'and', 'merge']) && is_array($item[1])) {
                    list($symbol, $subWhere) = $item;
                    if (!static::isMulti($subWhere)) {
                        $subWhere = [$subWhere];
                    }
                    list($subSql, $subParams) = static::where($subWhere, $id);
                    if (count($subWhere) > 1) {
                        $subSql = "({$subSql})";
                    }
                    if ($symbol == 'merge') {
                        $sql = $subSql;
                        if ($key != 0) {
                            throw new \PDOException(sprintf('This where only be the first: %s', json_encode($item)));
                        }
                    } else {
                        $sql .= " " . strtoupper($symbol) . " {$subSql}";
                        if ($key == 0) {
                            throw new \PDOException(sprintf('This where can\'t be the first: %s', json_encode($item)));
                        }
                    }
                    $params = array_merge($params, $subParams);
                    continue;
                }
                // 无值条件
                if (is_string($item[0]) && is_string($item[1])) {
                    list($field, $operator) = $item;
                    $operator = strtoupper($operator);
                    $subSql   = "{$field} {$operator}";
                    $sql      .= " AND {$subSql}";
                    if ($key == 0) {
                        $sql = $subSql;
                    }
                    continue;
                }
                throw new \PDOException(sprintf('Invalid where format: %s', json_encode($item)));
            } elseif ($length == 3) {
                // 标准条件 (包含In/NotIn/Between/NotBetween)
                list($field, $operator, $condition) = $item;
                $in      = in_array(strtoupper($operator), ['IN', 'NOT IN']);
                $between = in_array(strtoupper($operator), ['BETWEEN', 'NOT BETWEEN']);
                if (
                    (is_string($field) || $field instanceof Expr) && is_string($operator) && (is_scalar($condition) || $condition instanceof Expr) ||
                    (is_string($field) && ($in || $between) && is_array($condition))
                ) {
                    $subSql   = '';
                    $name     = $prefix . str_replace(['.', '`', ' ', '(', ')'], ['_', '', '', '_', '_'], $field);
                    $operator = strtoupper($operator);
                    if (!is_array($condition)) {
                        $subSql        = "{$field} {$operator} :{$name}";
                        $params[$name] = $condition;
                    } else {
                        if ($in) {
                            $subSql        = "{$field} {$operator} (:{$name})";
                            $params[$name] = $condition;
                        }
                        if ($between) {
                            $name1  = $prefix . 's_' . str_replace('.', '_', $field);
                            $name2  = $prefix . 'e_' . str_replace('.', '_', $field);
                            $subSql = "{$field} {$operator} :{$name1} AND :{$name2}";
                            list($condition1, $condition2) = $condition;
                            $params[$name1] = $condition1;
                            $params[$name2] = $condition2;
                        }
                    }
                    $sql .= " AND {$subSql}";
                    if ($key == 0) {
                        $sql = $subSql;
                    }
                    continue;
                }
                throw new \PDOException(sprintf('Invalid where format: %s', json_encode($item)));
            } else {
                throw new \PDOException(sprintf('Invalid where format: %s', json_encode($item)));
            }
        }
        return [$sql, $params];
    }

    /**
     * 构建Join条件
     * @param array $on
     * @return string
     */
    public static function joinOn(array $on)
    {
        $sql = '';
        if (!static::isMulti($on)) {
            $on = [$on];
        }
        foreach ($on as $key => $item) {
            $length = count($item);
            // php switch continue 非常奇葩，所以用 if else
            if ($length == 2) {
                list($symbol, $subOn) = $item;
                if (in_array($symbol, ['or', 'and']) && is_array($subOn)) {
                    if (!static::isMulti($subOn)) {
                        $subOn = [$subOn];
                    }
                    $subSql = static::joinOn($subOn);
                    if (count($subOn) > 1) {
                        $subSql = "({$subSql})";
                    }
                    $sql .= " " . strtoupper($symbol) . " {$subSql}";
                    continue;
                }
                throw new \PDOException(sprintf('Invalid join on format: %s', json_encode($item)));
            } elseif ($length == 3) {
                list($field, $operator, $condition) = $item;
                if (is_string($field) && is_string($operator) && is_scalar($condition)) {
                    $subSql = "{$field} {$operator} {$condition}";
                    $sql    .= " AND {$subSql}";
                    if ($key == 0) {
                        $sql = $subSql;
                    }
                    continue;
                }
                throw new \PDOException(sprintf('Invalid join on format: %s', json_encode($item)));
            } else {
                throw new \PDOException(sprintf('Invalid join on format: %s', json_encode($item)));
            }
        }
        return $sql;
    }

}
