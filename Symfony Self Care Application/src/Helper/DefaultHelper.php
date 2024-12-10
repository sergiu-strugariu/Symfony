<?php

namespace App\Helper;

use Exception;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use DateTime;
use DateTimeZone;
use Symfony\Contracts\Translation\TranslatorInterface;

class DefaultHelper
{
    const STATUS_DRAFT = 'draft';
    const STATUS_PUBLISHED = 'published';

    const ACTION_REMOVE = 'remove';
    const ACTION_MODERATE = 'moderate';

    const CATEGORY_TYPES = ['training', 'job', 'article', 'care', 'provider'];
    const SETTING_FIELDS = ['phone', 'helpLine', 'email', 'twitterLink', 'facebookLink', 'linkedinLink', 'instagramLink', 'logo', 'footerLogo', 'favicon'];
    const SETTING_FILE_FIELDS = ['logo', 'footerLogo', 'favicon'];
    const COMPANY_FILE_FIELDS = ['video' => 'videoPlaceholder', 'preview' => 'fileName', 'logo' => 'logo'];
    const EVENT_FILE_FIELDS = ['video' => 'videoPlaceholder', 'preview' => 'fileName', 'program' => 'programFileName'];

    const COMPANY_GALLERY = 'companyGallery';
    const EVENT_GALLERY = 'eventGallery';
    const EVENT_GALLERY_INTRO = 'eventGalleryIntro';

    const EVENT_GALLERIES = [self::EVENT_GALLERY, self::EVENT_GALLERY_INTRO, self::COMPANY_GALLERY];

    /**
     * @var string
     */
    private string $intercomSecretKey;

    /**
     * @var TranslatorInterface
     */
    private TranslatorInterface $translator;

    /**
     * @var KernelInterface
     */
    private KernelInterface $kernel;

    /**
     * @var ParameterBagInterface
     */
    private ParameterBagInterface $parameterBag;

    /**
     * @param KernelInterface $kernel
     * @param ParameterBagInterface $parameterBag
     * @param TranslatorInterface $translator
     * @param string $intercomSecretKey
     */
    public function __construct(KernelInterface $kernel, ParameterBagInterface $parameterBag, TranslatorInterface $translator, string $intercomSecretKey)
    {
        $this->kernel = $kernel;
        $this->parameterBag = $parameterBag;
        $this->translator = $translator;
        $this->intercomSecretKey = $intercomSecretKey;
    }

    /**
     * @param $param
     * @return string
     */
    public function getEnvValue($param): string
    {
        return $this->parameterBag->get($param);
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
     * @param DateTime $time
     * @return string
     * @throws Exception
     */
    public function getTimeAgo(DateTime $time): string
    {
        $now = new DateTime('now', new DateTimeZone('UTC'));
        $interval = $now->diff($time);

        if ($interval->y > 0) {
            $result = $this->translator->trans('times.job_time_ago', [], 'messages') . $interval->y . ($interval->y > 1 ? $this->translator->trans('times.year', [], 'messages') : $this->translator->trans('times.years', [], 'messages'));
        } elseif ($interval->m > 0) {
            $result = $this->translator->trans('times.job_time_ago', [], 'messages') . $interval->m . ($interval->m > 1 ? $this->translator->trans('times.months', [], 'messages') : $this->translator->trans('times.month', [], 'messages'));
        } elseif ($interval->d > 0) {
            $result = $this->translator->trans('times.job_time_ago', [], 'messages') . $interval->d . ($interval->d > 1 ? $this->translator->trans('times.days', [], 'messages') : $this->translator->trans('times.day', [], 'messages'));
        } elseif ($interval->h > 0) {
            $result = $this->translator->trans('times.job_time_ago', [], 'messages') . $interval->h . ($interval->h > 1 ? $this->translator->trans('times.hours', [], 'messages') : $this->translator->trans('times.hour', [], 'messages'));
        } elseif ($interval->i > 0) {
            $result = $this->translator->trans('times.job_time_ago', [], 'messages') . $interval->i . ($interval->i > 1 ? $this->translator->trans('times.minutes', [], 'messages') : $this->translator->trans('times.minute', [], 'messages'));
        } else {
            $result = $this->translator->trans('times.job_time', [], 'messages');
        }

        return $result;
    }

    /**
     * @param array $companies
     * @return int|string
     */
    public static function countCountyCodes(array $companies): int|string
    {
        $countyCodes = [];

        foreach ($companies['data'] as $company) {
            if (isset($company['county']['code'])) {
                $countyCodes[] = $company['county']['code'];
            }
        }

        $countyCount = array_count_values($countyCodes);
        arsort($countyCount);

        return array_key_first($countyCount);
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

    /**
     * @param array $data
     * @param string $year
     * @return array
     */
    public static function mappedDataForMonths(array $data, string $year): array
    {
        // Map data
        $mappedData = array_fill_keys(self::getCurrentMonths($year), 0);
        foreach ($data as $row) {
            $mappedData[$row['month']] = $row['count'];
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

    /**
     * @param $id
     * @return false|string
     */
    public function intercomGetHash($id): bool|string
    {
        return hash_hmac('sha256', $id, $this->intercomSecretKey);
    }

    /**
     * @return string[]
     */
    public static function getStatus(): array
    {
        return [
            self::STATUS_DRAFT => self::STATUS_DRAFT,
            self::STATUS_PUBLISHED => self::STATUS_PUBLISHED
        ];
    }

    /**
     * @param $cif
     * @return bool
     */
    public static function validateCIF($cif): bool
    {
        // Daca este string, elimina atributul fiscal si spatiile
        if (!is_int($cif)) {
            $cif = strtoupper($cif);
            if (str_starts_with($cif, 'RO')) {
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

    /**
     * @param string $iban
     * @return bool
     */
    public static function validateIBAN(string $iban): bool
    {
        // Remove spaces and convert to uppercase
        $iban = strtoupper(str_replace(' ', '', $iban));

        // Some quick simple tests to prevent needless work
        if (strlen($iban) < 5) {
            return false;
        }

        // Define country-specific IBAN patterns
        $bbancountrypatterns = [
            "AL" => "\\d{8}[\\dA-Z]{16}",
            "AD" => "\\d{8}[\\dA-Z]{12}",
            "AT" => "\\d{16}",
            "AZ" => "[\\dA-Z]{4}\\d{20}",
            "BE" => "\\d{12}",
            "BH" => "[A-Z]{4}[\\dA-Z]{14}",
            "BA" => "\\d{16}",
            "BR" => "\\d{23}[A-Z][\\dA-Z]",
            "BG" => "[A-Z]{4}\\d{6}[\\dA-Z]{8}",
            "CR" => "\\d{17}",
            "HR" => "\\d{17}",
            "CY" => "\\d{8}[\\dA-Z]{16}",
            "CZ" => "\\d{20}",
            "DK" => "\\d{14}",
            "DO" => "[A-Z]{4}\\d{20}",
            "EE" => "\\d{16}",
            "FI" => "\\d{14}",
            "FR" => "\\d{10}[\\dA-Z]{11}\\d{2}",
            "DE" => "\\d{18}",
            "GI" => "[A-Z]{4}[\\dA-Z]{15}",
            "GR" => "\\d{7}[\\dA-Z]{16}",
            "RO" => "[A-Z]{4}\\d{16}",
            "IE" => "[\\dA-Z]{4}\\d{14}",
            "IT" => "[A-Z]\\d{10}[\\dA-Z]{12}",
            "NL" => "[A-Z]{4}\\d{10}",
            "NO" => "\\d{11}",
            "PL" => "\\d{24}",
            "PT" => "\\d{21}",
            "SM" => "[A-Z]\\d{10}[\\dA-Z]{12}",
            "ES" => "\\d{20}",
            "SE" => "\\d{20}",
            "CH" => "\\d{5}[\\dA-Z]{12}",
            "TR" => "\\d{5}[\\dA-Z]{17}",
            // Add more countries as needed
        ];

        // Extract country code
        $countryCode = substr($iban, 0, 2);

        // Check if the country is supported
        if (!isset($bbancountrypatterns[$countryCode])) {
            return false;
        }

        // Validate the IBAN format using regex
        $bbanPattern = $bbancountrypatterns[$countryCode];
        $ibanRegexp = "/^[A-Z]{2}\\d{2}" . $bbanPattern . "$/";

        if (!preg_match($ibanRegexp, $iban)) {
            return false;
        }

        // Convert IBAN characters to digits (A=10, B=11, ..., Z=35)
        $ibanCheck = substr($iban, 4) . substr($iban, 0, 4);
        $ibanCheckDigits = '';
        foreach (str_split($ibanCheck) as $char) {
            $ibanCheckDigits .= is_numeric($char) ? $char : ord($char) - 55;
        }

        // Now process the IBAN check digits modulo 97 in a stepwise manner
        $mod97 = '';
        foreach (str_split($ibanCheckDigits, 7) as $block) {
            $mod97 = (int)($mod97 . $block) % 97;
        }

        return $mod97 === 1;
    }
}