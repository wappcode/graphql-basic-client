<?php

namespace GQLBasicClient;

class GQLQueryNameParser
{

    public $query;
    public $queryName;

    public function __construct(string $query)
    {
        $this->query = $query;
        $this->queryName = self::extractQueryName($query);
    }

    /**
     * Extrae el nombre de una consulta, mutacion o fragmento GraphQL.
     */
    public static function extractQueryName(string $query): ?string
    {
        // Captura query/mutation/fragment + nombre y valida el contexto siguiente.
        $pattern = '/^\s*(?:query|mutation|fragment)\s+([a-zA-Z_][a-zA-Z0-9_]*)\s*(?:\(|\{|on)/i';

        if (preg_match($pattern, trim($query), $matches)) {
            return $matches[1];
        }

        return null;
    }

    public function parseQueryName(): ?string
    {
        $this->queryName = self::extractQueryName($this->query);

        return $this->queryName;
    }

    public function __toString()
    {
        return $this->query;
    }
}
