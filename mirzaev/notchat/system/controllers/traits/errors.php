<?php

declare(strict_types=1);

namespace mirzaev\notchat\controllers\traits;

/**
 * Trait of handler of errors
 *
 * @package mirzaev\notchat\controllers\traits
 * @author Arsen Mirzaev Tatyano-Muradovich <arsen@mirzaev.sexy>
 */
trait errors
{
  private static function text(array $errors): array
  {
    // Initializing of output buffer
    $buffer = [];

    foreach ($errors as $offset => $error) {
      // Iterating through errors

      // Checking for nesting and writing to the output buffer (entry into recursion)
      if (isset($error['text'])) $buffer[] = $error['text'];
      else if (is_array($error) && count($error) > 0) $buffer[$offset] = static::text($error);
    }

    return $buffer;
  }
}

