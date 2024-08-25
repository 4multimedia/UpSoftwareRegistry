<?php

namespace Upsoftware\Registry\Classes;

use GusApi\Exception\InvalidUserKeyException;
use GusApi\Exception\NotFoundException;
use GusApi\GusApi;
use GusApi\ReportTypes;
use Illuminate\Validation\ValidationException;

class Poland
{
    public function getTypeFromValue($value): ?string
    {
        $value = preg_replace('/\D/', '', $value);

        if ($this->checkNip($value)) {
            return "nip";
        } elseif ($this->checkRegon($value)) {
            return "regon";
        } elseif ($this->checkKrs($value)) {
            return "krs";
        } elseif ($this->checkPesel($value)) {
            return "pesel";
        } else {
            return null;
        }
    }

    public function checkNip($value): bool
    {
        if (strlen($value) != 10) {
            return false;
        }

        if (preg_match('/^(\d)\1*$/', $value)) {
            return false;
        }

        $weights = [6, 5, 7, 2, 3, 4, 5, 6, 7];
        $sum = 0;

        for ($i = 0; $i < 9; $i++) {
            $sum += $value[$i] * $weights[$i];
        }

        $checksum = $sum % 11;

        return $checksum == $value[9];
    }

    public function checkRegon($value): bool
    {
        $length = strlen($value);

        if (preg_match('/^(\d)\1*$/', $value)) {
            return false;
        }

        if ($length == 9) {
            $weights = [8, 9, 2, 3, 4, 5, 6, 7];
        } elseif ($length == 14) {
            $weights = [2, 4, 8, 5, 0, 9, 7, 3, 6, 1, 2, 4, 8];
        } else {
            return false;
        }

        $sum = 0;

        for ($i = 0; $i < count($weights); $i++) {
            $sum += $value[$i] * $weights[$i];
        }

        $checksum = $sum % 11;
        if ($checksum == 10) $checksum = 0;

        return $checksum == $value[$length - 1];
    }

    public function checkKrs($value): bool {
        if (preg_match('/^(\d)\1*$/', $value)) {
            return false;
        }

        return strlen($value) == 10 && ctype_digit($value) && substr($value, 0, 2) === '00';
    }

    public function checkPesel($value): bool {
        if (preg_match('/^(\d)\1*$/', $value)) {
            return false;
        }

        if (strlen($value) != 11) {
            return false;
        }

        $weights = [1, 3, 7, 9, 1, 3, 7, 9, 1, 3];
        $sum = 0;

        for ($i = 0; $i < 10; $i++) {
            $sum += $value[$i] * $weights[$i];
        }

        $checksum = (10 - ($sum % 10)) % 10;

        return $checksum == $value[10];
    }

    public function getGusRegonApi($value, $type, $full = false): array
    {
        $gus = new GusApi(env('GUS_REGON_API'));

        try {
            $gus->login();
            if ($type === 'nip') {
                $gusReports = $gus->getByNip($value);
            } else if ($type === 'krs') {
                $gusReports = $gus->getByKrs($value);
            } else if ($type === 'regon') {
                $gusReports = $gus->getByRegon($value);
            } else {
                throw ValidationException::withMessages([
                    'type' => [trans('registry::validation.Błędny idetyfikator')],
                ]);
            }

            $array = [];
            if ($full) {
                $array["data"] = $gusReports;
                $reports = ReportTypes::REPORTS;
                foreach ($reports as $report) {
                    foreach ($gusReports as $gusReport) {
                        $fullReport = $gus->getFullReport($gusReport, $report);
                        if (!isset($fullReport[0]["ErrorCode"])) {
                            $array["reports"][] = $report;
                            $array[$report] = $fullReport;
                        }
                    }
                }
            } else {
                $array = $gusReports;
            }

            return $array;

        } catch (InvalidUserKeyException $e) {
            throw ValidationException::withMessages([
                'apikey' => [trans('registry::validation.Invalid api key')],
            ]);
        } catch (NotFoundException $e) {
            throw ValidationException::withMessages([
                'value' => [trans('registry::Not Found. '. $gus->getMessage())],
            ]);
        }
    }
}
