<?php

namespace App\Helper;

use DateTime;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class DefaultHelper
{
    const CATEGORY_TYPES = ['training', 'job', 'article', 'care', 'service'];

    /**
     * @var KernelInterface
     */
    private KernelInterface $kernel;
    private $intercomHash;

    private ParameterBagInterface $parameterBag;

    /**
     * @param KernelInterface $kernel
     */
    public function __construct(KernelInterface $kernel, $intercomHash, ParameterBagInterface $parameterBag)
    {
        $this->kernel = $kernel;
        $this->intercomHash = $intercomHash;
        $this->parameterBag = $parameterBag;
    }

    /**
     * @param $string
     * @return string
     */
    public static function generateHash($string): string
    {
        return sha1(mt_rand(1, 90000) . $string);
    }


    /**
     * @param string $string
     * @param int $length
     * @return string
     */
    public static function generateToken(string $string, int $length = 8): string
    {
        $start = rand(0, 16);
        $token = sha1(mt_rand(1, 90000) . $string);

        return substr($token, $start, $length);
    }

    /**
     * @param $machineName
     * @return array|mixed
     */
    public function parsePageJsonFile($machineName): mixed
    {
        $jsonPath = sprintf('%s/%s/%s.json', $this->kernel->getProjectDir(), 'config/pages', $machineName);

        // Check if the fileJson exists
        if (!file_exists($jsonPath)) {
            return [
                'success' => false,
                'message' => 'Json file does not exist.'
            ];
        }

        // Reading the contents of the file
        $jsonData = file_get_contents($jsonPath);

        // JSON decoding
        $pageTemplate = json_decode($jsonData, true);

        // Check for decoding errors
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'success' => false,
                'message' => 'Error decoding JSON: ' . json_last_error_msg()
            ];
        }

        return [
            'success' => true,
            'data' => $pageTemplate
        ];
    }

    /**
     * @throws \Exception
     */

    public function formatDatesForCourses($courses)
    {
        foreach ($courses as &$course) {
            if (is_object($course)) {
                $startDate = method_exists($course, 'getStartDate') ? $course->getStartDate() : null;
                $endDate = method_exists($course, 'getEndDate') ? $course->getEndDate() : null;
            } elseif (is_array($course)) {
                $startDate = isset($course['startDate']) && $course['startDate'] instanceof DateTime ? $course['startDate'] : null;
                $endDate = isset($course['endDate']) && $course['endDate'] instanceof DateTime ? $course['endDate'] : null;
            } else {
                continue;
            }

            if ($startDate instanceof DateTime && $endDate instanceof DateTime) {
                $startDay = $startDate->format('d');
                $endDay = $endDate->format('d');
                $year = $startDate->format('Y');

                $romanianMonths = [
                    'January' => 'ianuarie',
                    'February' => 'februarie',
                    'March' => 'martie',
                    'April' => 'aprilie',
                    'May' => 'mai',
                    'June' => 'iunie',
                    'July' => 'iulie',
                    'August' => 'august',
                    'September' => 'septembrie',
                    'October' => 'octombrie',
                    'November' => 'noiembrie',
                    'December' => 'decembrie',
                ];

                $formattedDate = sprintf('%s-%s %s %s', $startDay, $endDay, $romanianMonths[$startDate->format('F')], $year);

                if (is_object($course)) {
                    $course->formattedDate = $formattedDate;
                } elseif (is_array($course)) {
                    $course['formattedDate'] = $formattedDate;
                }
            } else {
                if (is_object($course)) {
                    $course->setFormattedDate('Invalid Date');
                } elseif (is_array($course)) {
                    $course['formattedDate'] = 'Invalid Date';
                }
            }
        }

        return $courses;
    }

    /**
     * @param string $year
     * @return array
     */
    public static function getCurrentMonths(string $year): array
    {
        $currentYear = (new DateTime())->format('Y');
        $currentMonth = (new DateTime())->format('n');

        $months = range(1, 12);
        if ($year == $currentYear) {
            $months = range(1, $currentMonth);
        }

        return $months;
    }

    public static function mappedDataForMonths(array $data, string $year): array
    {
        // Map data
        $mappedData = array_fill_keys(self::getCurrentMonths($year), 0);

        foreach ($data as $row) {
            if (isset($row['count'])) {
                $mappedData[$row['month']] = $row['count'];
            }

            if (isset($row['price'])) {
                $mappedData[$row['month']] = intval($row['price']);
            }
        }

        // Transform data in valid format
        $labels = array_map(function ($month) {
            // 'F' returns the full month name
            return DateTime::createFromFormat('!m', $month)->format('M');
        }, array_keys($mappedData));

        $values = array_values($mappedData);

        return [
            'labels' => $labels,
            'values' => $values
        ];
    }

    public static function validateCIF($cif)
    {
        // Daca este string, elimina atributul fiscal si spatiile
        if (!is_int($cif)) {
            $cif = strtoupper($cif);
            if (strpos($cif, 'RO') === 0) {
                $cif = substr($cif, 2);
            }
            $cif = (int)trim($cif);
        }

        // daca are mai mult de 10 cifre sau mai putin de 2, nu-i valid
        if (strlen($cif) > 10 || strlen($cif) < 2) {
            return false;
        }

        // numarul de control
        $v = 753217532;

        // extrage cifra de control
        $c1 = $cif % 10;
        $cif = (int)($cif / 10);

        // executa operatiile pe cifre
        $t = 0;
        while ($cif > 0) {
            $t += ($cif % 10) * ($v % 10);
            $cif = (int)($cif / 10);
            $v = (int)($v / 10);
        }


        // aplica inmultirea cu 10 si afla modulo 11
        $c2 = $t * 10 % 11;

        // daca modulo 11 este 10, atunci cifra de control este 0
        if ($c2 == 10) {
            $c2 = 0;
        }

        return $c1 === $c2;
    }

    public function intercomGetHash($id)
    {
        return hash_hmac(
            'sha256',
            $id,
            $this->intercomHash
        );
    }

    /**
     * @param $recaptcha
     * @return bool
     */
    public function captchaVerify($recaptcha): bool
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->getEnvValue('recaptcha_site_verify'));
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, ["secret" => $this->getEnvValue('recaptcha_secret_key'), "response" => $recaptcha]);

        $response = curl_exec($ch);

        if ($response === false) {
            // Handle cURL error
            $error = curl_error($ch);
            curl_close($ch);

            return true;
        }

        curl_close($ch);
        $data = json_decode($response);

        if (JSON_ERROR_NONE !== json_last_error()) {
            return true;
        }

        return !$data->success;
    }

    /**
     * @param $param
     * @return string
     */
    public function getEnvValue($param): string
    {
        return $this->parameterBag->get($param);
    }
}