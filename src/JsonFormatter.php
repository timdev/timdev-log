<?php

declare(strict_types=1);

namespace TimDev\Log;

use Monolog\Formatter\JsonFormatter as MonologJsonFormatter;

/**
 * An opinionated JsonFormatter for monolog.
 *
 * If Monolog's JsonFormatter would write something like:
 *
 * {"datetime": "...", "message": "A thing happened", "context": {"foo: "bar"}}
 *
 * This formatter instead writes:
 *
 * {"ts": "1632597376.699702", "msg": "A thing happened", "foo": "bar" }
 *
 * Specifically:
 *   - Renames 'message' => 'msg' and 'datetime' => 'ts'.
 *   - "Hoists" values in 'context' to the top level of the object.
 *   - Formats log timestamps as 'U.u'.
 *   - Includes stack traces in logged exceptions.
 */
class JsonFormatter extends MonologJsonFormatter
{
    protected $includeStacktraces = true;

    protected function normalize($data, int $depth = 0)
    {
        // Monolog calls normalize recursively on arrays, and we only care about
        // the top-level array here. So we test that $data is an array, and that
        // $depth is zero.
        if (is_array($data) && $depth === 0) {
            $this->renameElement($data, 'message', 'msg');
            $this->renameElement($data, 'datetime', 'ts');
            $this->translateTimestamp($data);
            $this->hoistContext($data);
        }
        return parent::normalize($data, $depth);
    }

    private function hoistContext(array &$rec): void
    {
        /** @var mixed[]|mixed|false $context */
        $context = $rec['context'] ?? false;
        if (is_array($context)) {
            $rec += $context;
            unset($rec['context']);
        }
    }

    private function renameElement(array &$rec, string $old, string $new): void
    {
        if (isset($rec[$old])) {
            /** @psalm-var mixed */
            $rec[$new] = $rec[$old];
            unset($rec[$old]);
        }
    }

    private function translateTimestamp(array &$rec): void
    {
        if (isset($rec['ts']) && $rec['ts'] instanceof \DateTimeInterface) {
            $rec['ts'] = $rec['ts']->format('U.u');
        }
    }
}
