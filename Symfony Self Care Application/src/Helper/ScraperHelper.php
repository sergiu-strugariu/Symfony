<?php

namespace App\Helper;

use Symfony\Component\Panther\Client;

class ScraperHelper
{
    
    private $chromeDriverPath;
    private $csvFilePath;
    
    /**
     * @param $kernelProjectDir
     */
    public function __construct(string $kernelProjectDir)
    {
        $this->chromeDriverPath = sprintf('%s/%s', $kernelProjectDir, '/vendor/bin/drivers/chromedriver');
        $this->csvFilePath = sprintf('%s/%s', $kernelProjectDir, '/public/assets/csv/scrapper.csv');
    }
 
    public function scrapCamineBatrani() {
        set_time_limit(0);
        
        $client = Client::createChromeClient($this->chromeDriverPath, [
                    "--user-agent=Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36"
        ]);

        $links = [
            // insert links here
        ];

        $elements = [];
        foreach ($links as $link) {
            $crawler = $client->request('GET', $link);

            try {
                $name = $crawler->filter("h1")->eq(0)->text();
            } catch (\Exception $e) {
                $name = '';
            }
            try {
                $email = $crawler->filter(".lp-listing-phone-whatsapp")->eq(0)->text();
            } catch (\Exception $e) {
                $email = '';
            }
            try {
                $city = $crawler->filter(".lp-listing-additional-details ul li span")->eq(0)->text();
            } catch (\Exception $e) {
                $city = '';
            }
            try {
                $address = $crawler->filter("#lp-respo-direc")->eq(0)->text();
            } catch (\Exception $e) {
                $address = '';
            }
            try {
                $description = $crawler->filter(".lp-listing-desription")->eq(0)->text();
            } catch (\Exception $e) {
                $description = '';
            }
            try {
                $services = [];
                $crawler->filter(".features li")->each(function ($htmlElement) use (&$services) {
                    $services[] = $htmlElement->text();
                });
            } catch (\Exception $e) {
                $services = [];
            }
            try {
                $gallery = [];
                $crawler->filter(".lp-listing-slider .slick-slide:not(.slick-cloned)")->each(function ($htmlElement) use (&$gallery) {
                    $gallery[] = $htmlElement->filter("a")->eq(0)->getAttribute("href");
                });
            } catch (\Exception $e) {
                $gallery = [];
            }

            $element = [
                'name' => trim(preg_replace('/\s+/', ' ', $name)),
                'email' => $email,
                'county' => '',
                'city' => $city,
                'address' => $address,
                'zipCode' => '',
                'admissionCriterias' => '',
                'price' => '',
                'services' => implode(", ", $services),
                'careCategories' => '',
                'mainPhoto' => '',
                'logo' => '',
                'videoLink' => '',
                'videoThumb' => '',
                'gallery' => implode(", ", $gallery),
                'shortDescription' => '',
                'description' => trim(preg_replace('/\s+/', ' ', $description))
            ];

            $elements[] = $element;
        }

        $this->writeToCSV($elements);
       
        return $elements;
    }
    
    public function scrapBatraniFericiti() {
        set_time_limit(0);
        
        $client = Client::createChromeClient($this->chromeDriverPath, [
                    "--user-agent=Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36"
        ]);

        $links = [
            // insert links here
        ];

        $elements = [];
        foreach ($links as $link) {
            $crawler = $client->request('GET', $link);
            
            try {
                $name = $crawler->filter("h1")->eq(0)->text();
            } catch (\Exception $e) {
                $name = '';
            }
            $email = '';
            try {
                $address = $crawler->filter("h6")->eq(0)->text();
            } catch (\Exception $e) {
                $address = '';
            }
            try {
                $mainPhoto = $crawler->filter("img.logo")->eq(0)->getAttribute("src");
            } catch (\Exception $e) {
                $mainPhoto = '';
            }
            try {
                $logo = $crawler->filter("img.logo")->eq(1)->getAttribute("src");
            } catch (\Exception $e) {
                $logo = '';
            }
            try {
                $gallery = [];
                $crawler->filter("#company-photos .col-md-3 ")->each(function ($htmlElement) use (&$gallery) {
                    $gallery[] = $htmlElement->filter("a")->eq(0)->getAttribute("href");
                });
            } catch (\Exception $e) {
                $gallery = [];
            }

            $element = [
                'name' => trim(preg_replace('/\s+/', ' ', $name)),
                'email' => $email,
                'county' => '',
                'city' => '',
                'address' => $address,
                'zipCode' => '',
                'admissionCriterias' => '',
                'price' => '',
                'services' => '',
                'careCategories' => '',
                'mainPhoto' => $mainPhoto,
                'logo' => $logo,
                'videoLink' => '',
                'videoThumb' => '',
                'gallery' => implode(", ", $gallery),
                'shortDescription' => '',
                'description' => ''
            ];

            $elements[] = $element;
        }

        $this->writeToCSV($elements);

        return $elements;
    }
    
    public function scrapAzilBatrani() {
        set_time_limit(0);
        
        $client = Client::createChromeClient($this->chromeDriverPath, [
                    "--user-agent=Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36"
        ]);

        $links = [
            // insert links here
        ];

        $elements = [];
        foreach ($links as $link) {
            $crawler = $client->request('GET', $link);
            
            try {
                $name = $crawler->filter("h2")->eq(0)->text();
            } catch (\Exception $e) {
                $name = '';
            }
            try {
                $email = $crawler->filter("span[itemprop='email']")->eq(0)->text();
            } catch (\Exception $e) {
                $email = '';
            }
            try {
                $city = $crawler->filter("span[itemprop='addressLocality']")->eq(0)->text();
            } catch (\Exception $e) {
                $city = '';
            }
            try {
                $county = $crawler->filter("span[itemprop='addressRegion']")->eq(0)->text();
            } catch (\Exception $e) {
                $county = '';
            }
            try {
                $mainPhoto = $crawler->filter(".blog-text img")->eq(0)->getAttribute("src");
            } catch (\Exception $e) {
                $mainPhoto = '';
            }
            try {
                $logo = $crawler->filter("img[itemprop='image']")->eq(0)->getAttribute("src");
            } catch (\Exception $e) {
                $logo = '';
            }
            try {
                $gallery = [];
                $crawler->filter(".galeriefoto .col-md-4")->each(function ($htmlElement) use (&$gallery) {
                    $gallery[] =  sprintf('%s%s', 'https://www.azil-batrani.ro', $htmlElement->filter("a")->eq(0)->getAttribute("href"));
                });
            } catch (\Exception $e) {
                $gallery = [];
            }

            $element = [
                'name' => trim(preg_replace('/\s+/', ' ', $name)),
                'email' => $email,
                'county' => $county,
                'city' => $city,
                'address' => '',
                'zipCode' => '',
                'admissionCriterias' => '',
                'price' => '',
                'services' => '',
                'careCategories' => '',
                'mainPhoto' => !empty($mainPhoto) ? sprintf('%s%s', 'https://www.azil-batrani.ro', $mainPhoto) : $mainPhoto,
                'logo' => !empty($logo) ? sprintf('%s%s', 'https://www.azil-batrani.ro', $logo) : $logo,
                'videoLink' => '',
                'videoThumb' => '',
                'gallery' => implode(", ", $gallery),
                'shortDescription' => '',
                'description' => ''
            ];

            $elements[] = $element;
        }

        $this->writeToCSV($elements);

        return $elements;
    }
    
    public function scrapiCamin() {
        set_time_limit(0);
        
        $client = Client::createChromeClient($this->chromeDriverPath, [
                    "--user-agent=Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36"
        ]);

        $links = [
            // insert links here
        ];

        $elements = [];
        foreach ($links as $link) {
            $crawler = $client->request('GET', $link);

            try {
                $name = $crawler->filter("h3")->eq(0)->text();
            } catch (\Exception $e) {
                $name = '';
            }
            try {
                $county = $crawler->filter(".breadcrumb-item")->eq(2)->text();
            } catch (\Exception $e) {
                $county = '';
            }
            try {
                $mainPhoto = $crawler->filter(".card-body img")->eq(0)->getAttribute("src");
            } catch (\Exception $e) {
                $mainPhoto = '';
            }
            try {
                $description = $crawler->filter(".card-body ul")->eq(0)->text();
            } catch (\Exception $e) {
                $description = '';
            }

            $element = [
                'name' => trim(preg_replace('/\s+/', ' ', $name)),
                'email' => '',
                'county' => $county,
                'city' => '',
                'address' => '',
                'zipCode' => '',
                'admissionCriterias' => '',
                'price' => '',
                'services' => '',
                'careCategories' => '',
                'mainPhoto' => !empty($mainPhoto) ? sprintf('%s%s', 'https://icamin.ro', $mainPhoto) : $mainPhoto,
                'logo' => '',
                'videoLink' => '',
                'videoThumb' => '',
                'gallery' => '',
                'shortDescription' => '',
                'description' => $description
            ];

            $elements[] = $element;
        }

        $this->writeToCSV($elements);

        return $elements;
    }
    
    private function writeToCSV($data) {
        // open CSV file
        $csvFile = fopen($this->csvFilePath, "a");

        // add each element to the CSV file
        foreach ($data as $element) {
            fputcsv($csvFile, $element, ";", '"');
        }

        // close the CSV file
        fclose($csvFile);
    }

}