<?php

namespace common\components;

use Yii;
use yii\base\Component;

/**
 * Компонент для экранирования данных, согласно массиву правил, перед выводом в браузер
 *
 * @package common\components
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class UserScreener extends Component
{
    private const DEFAULT_RULES = [
        'purifyHtml',
        'htmlentities' => ['double_encode' => false],
        'htmlspecialchars_decode',
    ];
    private const STRING_RULES = [
        'nl2br',
        'trim',
        'ltrim',
        'rtrim',
        'strip_tags',
        'htmlspecialchars',
        'htmlspecialchars_decode',
        'htmlentities',
        'html_entity_decode',
        'strtolower',
        'strtoupper',
        'ucfirst',
        'lcfirst',
        'ucwords',
        'purifyHtml',
    ];

    private const TRIM_DEFAULT_CHARACTERS = " \t\n\r\0\x0B";

    private const ENT_DEFAULT_FLAGS = ENT_QUOTES | ENT_SUBSTITUTE;

    private const UCWORDS_DEFAULT_SEPARATORS = " \t\r\n\f\v";

    final public function rulesByKey(array $rules, string $key): array
    {
        $keyRules = [];
        foreach ($rules as $rule) {
            if (is_array($rule) && isset($rule[0], $rule[1])) {
                $fields = $rule[0];
                if ($fields === $key || (is_array($fields) && in_array($key, $fields, true))) {
                    $keyRules[$rule[1]] = array_slice($rule, 2);
                }
            }
        }
        if (empty($keyRules)) {
            $keyRules = self::DEFAULT_RULES;
        }
        return $keyRules;
    }

    /**
     * Экранирование текста
     */
    final public function prepareText(mixed $string = null, array $rules = self::DEFAULT_RULES): mixed
    {
        if (!is_string($string)) {
            return $string;
        }
        foreach ($rules as $rule => $options) {
            if (is_int($rule)) {
                $rule = $options;
                $options = null;
            }
            if (!in_array($rule, self::STRING_RULES, true)) {
                continue;
            }
            switch ($rule) {
                case 'trim':
                    $string = trim($string, $options['characters'] ?? self::TRIM_DEFAULT_CHARACTERS);
                    break;
                case 'ltrim':
                    $string = ltrim($string, $options['characters'] ?? self::TRIM_DEFAULT_CHARACTERS);
                    break;
                case 'rtrim':
                    $string = rtrim($string, $options['characters'] ?? self::TRIM_DEFAULT_CHARACTERS);
                    break;
                case 'strip_tags':
                    $string = strip_tags($string);
                    break;
                case 'htmlspecialchars':
                    $string = htmlspecialchars(
                        $string,
                        $options['flags'] ?? self::ENT_DEFAULT_FLAGS,
                        $options['encoding'] ?? null,
                        $options['double_encode'] ?? true,
                    );
                    break;
                case 'htmlspecialchars_decode':
                    $string = htmlspecialchars_decode($string, $options['flags'] ?? self::ENT_DEFAULT_FLAGS);
                    break;
                case 'htmlentities':
                    $string = htmlentities(
                        $string,
                        $options['flags'] ?? self::ENT_DEFAULT_FLAGS,
                        $options['encoding'] ?? null,
                        $options['double_encode'] ?? true,
                    );
                    break;
                case 'html_entity_decode':
                    $string = html_entity_decode(
                        $string,
                        $options['flags'] ?? self::ENT_DEFAULT_FLAGS,
                        $options['encoding'] ?? null,
                    );
                    break;
                case 'strtolower':
                    $string = mb_strtolower($string);
                    break;
                case 'strtoupper':
                    $string = mb_strtoupper($string);
                    break;
                case 'ucfirst':
                    $string = ucfirst($string);
                    break;
                case 'lcfirst':
                    $string = lcfirst($string);
                    break;
                case 'ucwords':
                    $string = ucwords($string, $options['separators'] ?? self::UCWORDS_DEFAULT_SEPARATORS);
                    break;
                case 'nl2br':
                    $string = nl2br($string);
                    break;
                case 'purifyHtml':
                    $string = Yii::$app->formatter->asHtml($string, $options);
                    break;
            }
        }
        return $string;
    }
}
