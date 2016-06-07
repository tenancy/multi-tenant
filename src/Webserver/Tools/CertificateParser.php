<?php

namespace Hyn\Webserver\Tools;

use Carbon\Carbon;
use phpseclib\File\X509;

class CertificateParser
{
    /**
     * @var X509
     */
    protected $x509;

    /**
     * @var mixed
     */
    protected $x509result;

    public function __construct($certificate)
    {
        $x509 = new X509();
        $x509result = $x509->loadX509($certificate);

        $this->x509 = $x509;
        $this->x509result = $x509result;
    }

    /**
     * Loads all hostnames in the certificate.
     *
     * @return array
     */
    public function getHostnames()
    {
        // get all certificate alt names
        $altNames = $this->x509->getExtension('id-ce-subjectAltName');
        if ($altNames) {
            array_walk($altNames, function (&$value, $key) {
                $value = $value['dNSName'];
            });
        } else {
            $altNames = [];
        }

        $hostnames = array_merge(
            $this->x509->getDNProp('CN') ?: [],
            $altNames
        );
        // array unique removes possible duplicates from CN also in altnames
        return array_unique($hostnames);
    }

    /**
     * Certificate signature algorithm.
     *
     * @return string
     */
    public function getAlgorithm()
    {
        return array_get($this->x509result, 'tbsCertificate.signature.algorithm');
    }

    /**
     * @return Carbon
     */
    public function getValidityFrom()
    {
        $validity = array_get($this->x509result, 'tbsCertificate.validity');

        return $validity ? new Carbon(array_get($validity, 'notBefore.utcTime')) : null;
    }

    /**
     * @return Carbon
     */
    public function getValidityTo()
    {
        $validity = array_get($this->x509result, 'tbsCertificate.validity');

        return $validity ? new Carbon(array_get($validity, 'notAfter.utcTime')) : null;
    }

    /**
     * Checks whether certificate is SAN.
     *
     * @return bool
     */
    public function isSAN()
    {
        $hostnames = $this->getHostnames();
        $uniqueHostnames = 0;
        foreach ($hostnames as $hostname) {
            // count www and non-www as one
            // count * and non-www as one
            if (preg_match('/^((www)|\*)\.(?<parent>.*)$/', $hostname, $m) && in_array($m['parent'], $hostnames)) {
                continue;
            }
            $uniqueHostnames++;
        }

        return $uniqueHostnames > 1;
    }

    /**
     * Checks whether certificate is or has wildcard.
     *
     * @return bool
     */
    public function isWildcard()
    {
        $hostnames = $this->getHostnames();
        foreach ($hostnames as $hostname) {
            if (substr($hostname, 0, 2) == '*.') {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the issuer organization name.
     *
     * @return string
     */
    public function issuer()
    {
        $issuer = $this->x509->getIssuerDNProp('id-at-organizationName');

        return head($issuer);
    }

    /**
     * Tries to find the best match of the current certificate.
     *
     * @return string
     */
    public function type()
    {
        $types = $this->x509->getSubjectDNProp('id-at-organizationalUnitName');

        return last($types);
    }
}
