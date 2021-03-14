<?php
/**
 * Created by PhpStorm.
 * User: radon
 * Date: 10.03.2019
 * Time: 22:06
 */

namespace Mangadex\Model;


use GeoIp2\Database\Reader;
use GeoIp2\Exception\AddressNotFoundException;
use GeoIp2\Model\City;
use GeoIp2\Model\Country;
use MaxMind\Db\Reader\InvalidDatabaseException;

class IpLocator
{

    private $countryDb;

    private $cityDb;

    private $isInitialized = false;

    public function __construct()
    {
        try {
            $this->countryDb = new Reader(ABSPATH . '/GeoLite2-Country.mmdb');
            $this->cityDb = new Reader(ABSPATH . '/GeoLite2-City.mmdb');
        } catch (InvalidDatabaseException $e) {
            // Send to sentry
            trigger_error($e->getMessage(), E_USER_WARNING);
        } finally {
            $this->isInitialized = true;
        }
    }

    public function getCountryFromIp(string $ip)
    {
        if (!$this->isInitialized)
            return null;

        try {
            $record = $this->countryDb->country($ip);
            if (!$record instanceof Country)
                return null;

            return $record->country->name
                ?? $record->registeredCountry->name
                ?? $record->representedCountry->name
                ?? '<Unknown Country>';
        } catch (AddressNotFoundException $e) {
            return null;
        } catch (\Throwable $t) {
            return null;
        }
    }

    public function getCountryCodeFromIp(string $ip)
    {
        if (!$this->isInitialized)
            return null;

        try {
            $record = $this->countryDb->country($ip);
            if (!$record instanceof Country)
                return null;

            $cc = $record->country->isoCode
                ?? $record->registeredCountry->isoCode
                ?? $record->representedCountry->isoCode
                ?? '??';
            return strtoupper($cc);
        } catch (AddressNotFoundException $e) {
            return null;
        } catch (\Throwable $t) {
            return null;
        }
    }

    public function getCityFromIp(string $ip)
    {
        if (!$this->isInitialized)
            return null;

        try {
            $record = $this->cityDb->city($ip);
            if (!$record instanceof City)
                return null;

            return $record->city->name
                ?? '<Unknown City>';
        } catch (AddressNotFoundException $e) {
            return null;
        } catch (\Throwable $t) {
            return null;
        }
    }

    public function getCityRecord(string $ip)
    {
        try {
            return $this->cityDb->city($ip);
        } catch (AddressNotFoundException $e) {
            return null;
        } catch (\Throwable $t) {
            return null;
        }
    }

    public function getCountryRecord(string $ip)
    {
        try {
            return $this->countryDb->country($ip);
        } catch (AddressNotFoundException $e) {
            return null;
        } catch (\Throwable $t) {
            return null;
        }
    }

}