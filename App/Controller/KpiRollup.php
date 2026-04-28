<?php

namespace App\Controller;

use App\Library\Kpi\KpiMinuteRollup;
use Glial\Synapse\Controller;

class KpiRollup extends Controller
{
    public function rollupMinute($param)
    {
        $this->view = false;
        $this->layout_name = false;

        if (!IS_CLI) {
            return;
        }

        $result = KpiMinuteRollup::rollupMinute(self::parseOptions((array)$param));

        echo json_encode($result, JSON_UNESCAPED_SLASHES).PHP_EOL;
    }

    private static function parseOptions(array $param): array
    {
        $options = [];
        foreach ($param as $value) {
            if (!is_scalar($value)) {
                continue;
            }

            $value = trim((string)$value);
            if ($value === '' || strpos($value, ':') === false) {
                continue;
            }

            [$key, $optionValue] = explode(':', $value, 2);
            if ($key === 'max_buckets') {
                $options['max_buckets'] = (int)$optionValue;
            } elseif ($key === 'now') {
                $options['now'] = $optionValue;
            }
        }

        return $options;
    }
}
