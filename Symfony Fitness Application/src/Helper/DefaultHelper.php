<?php

namespace App\Helper;

use DateTime;
use Symfony\Component\HttpKernel\KernelInterface;

class DefaultHelper
{
    const CATEGORY_TYPES = ['training', 'job', 'article', 'care', 'service'];

    /**
     * @var KernelInterface
     */
    private KernelInterface $kernel;

    /**
     * @param KernelInterface $kernel
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
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

}