<?php

namespace App\Helpers;

class SearchHelper
{
    public static function getFuzzySearchCondition($column, $searchTerm, $maxDistance = 3)
    {
        // Dividi la stringa di ricerca in parole
        $words = explode(' ', $searchTerm);
        $conditions = [];

        foreach ($words as $word) {
            if (strlen($word) > 3) {  // Applica fuzzy search solo per parole più lunghe di 3 caratteri
                $conditions[] = "LEVENSHTEIN($column, ?) <= $maxDistance";
            } else {
                $conditions[] = "$column LIKE ?";
            }
        }

        return [
            'condition' => '(' . implode(' OR ', $conditions) . ')',
            'bindings' => array_map(function($word) {
                return strlen($word) > 3 ? $word : "%$word%";
            }, $words)
        ];
    }
} 