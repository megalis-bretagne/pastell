<?php

declare(strict_types=1);

namespace Pastell\Utilities;

use DivisionByZeroError;

class Certificate
{
    private bool $isValid;
    /**
     * @var string[]
     */
    private array $subject;

    /**
     * @var string[]
     */
    private array $issuer;
    private string $name;
    private string $serialNumber;
    private int $validFrom;
    private int $validTo;

    public function __construct(private string $certificatPEM)
    {
        $certData = openssl_x509_parse($this->certificatPEM) ;
        if (! $certData) {
            $this->isValid = false;
            return;
        }
        $this->isValid = true;

        foreach ($certData['subject'] as $name => $value) {
            if (is_array($value)) {
                $this->subject[$name] = '';
                continue;
            }
            $this->subject[$name] = $value;
        }

        foreach ($certData['issuer'] as $name => $value) {
            if (is_array($value)) {
                $this->issuer[$name] = '';
                continue;
            }
            $this->issuer[$name] = $value;
        }

        $this->name = $certData['name'];
        $this->serialNumber = $certData['serialNumber'];
        $this->validFrom = $certData['validFrom_time_t'];
        $this->validTo = $certData['validTo_time_t'];
    }

    public function isValid(): bool
    {
        return  $this->isValid;
    }

    public function getContent(): string
    {
        return $this->certificatPEM;
    }

    public function getMD5(): string
    {
        if (! $this->isValid()) {
            return '';
        }
        $chaine = 'subject:';
        foreach ($this->subject as $name => $value) {
            $chaine .= "$name=$value/";
        }
        $chaine .= ';issuer=';
        foreach ($this->issuer as $name => $value) {
            $chaine .= "$name=$value/";
        }

        return md5($chaine);
    }

    public function getName(): string
    {
        if (! $this->isValid) {
            return '';
        }
        return $this->name;
    }

    //http://stackoverflow.com/questions/6426438/php-ssl-certificate-serial-number-in-hexadecimal

    /**
     * @throws DivisionByZeroError
     * @return string
     */
    public function getSerialNumber(): string
    {
        $base = bcpow('2', '32');
        $counter = 100;
        $res = '';
        if (! $this->isValid) {
            return '';
        }
        $val = $this->serialNumber;
        while ($counter > 0 && $val > 0) {
            --$counter;
            $tmpString = dechex((int) bcmod($val, $base));
            for ($i = 8 - strlen($tmpString); $i > 0; --$i) {
                $tmpString = "0$tmpString";
            }
            $res = $tmpString . $res;
            $val = bcdiv($val, $base);
        }
        if ($counter <= 0) {
            return '';
        }
        return strtoupper($res);
    }

    /**
     * @return string[]
     */
    public function getIssuerAsArray(): array
    {
        return $this->issuer;
    }

    /**
     * @return string[]
     */
    public function getSubjectAsArray(): array
    {
        return $this->subject;
    }

    public function getIssuer(): string
    {
        $data = [];
        foreach ($this->issuer as $name => $value) {
            $data[] = "$name=$value";
        }

        return '/' . implode('/', $data);
    }

    public function getValidFrom(): int
    {
        return $this->validFrom;
    }

    public function getValidTo(): int
    {
        return $this->validTo;
    }
}
