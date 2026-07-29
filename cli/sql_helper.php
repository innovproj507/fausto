<?php

/**
 * Split a plain SQL dump into individual statements. Good enough for this
 * project's dumps (DDL + a few short lookup-table INSERTs, no stored
 * procedures/DELIMITER blocks) - not a general-purpose SQL parser.
 */
function splitSqlStatements(string $sql): array
{
    // These dumps are CRLF (Windows); normalize before splitting on "\n".
    $sql = str_replace("\r\n", "\n", $sql);

    // Drop full-line "-- " comments first. Doing this before splitting on
    // ";" matters: a comment line directly above a CREATE TABLE ends up in
    // the same chunk as that statement, so checking "does this chunk start
    // with --" (instead of stripping comment lines up front) would wrongly
    // discard the real statement along with the comment.
    $lines = array_filter(
        explode("\n", $sql),
        fn($line) => !str_starts_with(trim($line), '--')
    );
    $sql = implode("\n", $lines);

    $statements = [];
    foreach (explode(";\n", $sql) as $chunk) {
        $chunk = trim($chunk);
        if ($chunk === '') {
            continue;
        }

        // These dumps hardcode `CREATE DATABASE IF NOT EXISTS ecommerce` /
        // `USE ecommerce` at the top. Executing that USE would silently
        // redirect every statement after it to the literal `ecommerce`
        // database regardless of which database the connection was opened
        // against (DB_DATABASE in .env) - always skip both; the target
        // database is whatever the PDO connection's DSN already selected.
        if (preg_match('/^(CREATE\s+DATABASE|USE)\b/i', $chunk)) {
            continue;
        }

        // Some dumps end with a couple of "-- Verificar" SELECT ... LIMIT 5
        // sanity-check queries left over from HeidiSQL's export. They have
        // no schema/data effect and PDO::exec() on a bare SELECT can throw
        // "unbuffered queries are active" - just skip them.
        if (preg_match('/^SELECT\b/i', $chunk)) {
            continue;
        }

        $statements[] = $chunk;
    }
    return $statements;
}
