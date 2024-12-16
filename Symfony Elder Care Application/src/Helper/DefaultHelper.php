<?php

namespace App\Helper;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class DefaultHelper
{
    const STATUS_DRAFT = 'draft';
    const STATUS_PUBLISHED = 'published';

    const SETTING_FIELDS = ['phone', 'helpLine', 'email', 'twitterLink', 'facebookLink', 'linkedinLink', 'instagramLink', 'logo', 'footerLogo', 'authImage', 'favicon'];
    const SETTING_FILE_FIELDS = ['logo', 'footerLogo', 'favicon', 'authImage'];

    private ParameterBagInterface $parameterBag;

    /**
     * @var KernelInterface
     */
    private KernelInterface $kernel;

    public function __construct(KernelInterface $kernel, ParameterBagInterface $parameterBag)
    {
        $this->kernel = $kernel;
        $this->parameterBag = $parameterBag;
    }

    /**
     * @param $param
     * @return string
     */
    public function getEnvValue($param): string
    {
        return $this->parameterBag->get($param);
    }

    public function parsePageJsonFile($machineName)
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
     * @param $errors
     * @return string
     */
    public function parseBackendError($errors): string
    {
        $errMessages = [];

        foreach ($errors as $error) {
            $errMessages[] = $error->getMessage();
        }

        return implode('<br/>', $errMessages);
    }

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
}