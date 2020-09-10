<?php

namespace Pastell;

use Exception;
use ParagonIE\ConstantTime\Hex;
use ParagonIE\Halite\Alerts\CannotPerformOperation;
use ParagonIE\Halite\Alerts\InvalidDigestLength;
use ParagonIE\Halite\Alerts\InvalidKey;
use ParagonIE\Halite\Alerts\InvalidMessage;
use ParagonIE\Halite\Alerts\InvalidSalt;
use ParagonIE\Halite\Alerts\InvalidSignature;
use ParagonIE\Halite\Alerts\InvalidType;
use ParagonIE\Halite\KeyFactory;
use ParagonIE\Halite\Symmetric\Crypto as HaliteSymmetricCrypto;
use ParagonIE\HiddenString\HiddenString;

class Crypto
{

    /**
     * @throws CannotPerformOperation
     * @throws InvalidDigestLength
     * @throws InvalidKey
     * @throws InvalidMessage
     * @throws InvalidSalt
     * @throws InvalidType
     * @throws Exception
     */
    public function encrypt(string $message, string $password): string
    {
        $salt = random_bytes(16);

        $encryptionKey = KeyFactory::deriveEncryptionKey(new HiddenString($password), $salt);

        return json_encode([
            'salt' => Hex::encode($salt),
            'message' => HaliteSymmetricCrypto::encrypt(new HiddenString($message), $encryptionKey)
        ]);
    }

    /**
     * @throws CannotPerformOperation
     * @throws InvalidDigestLength
     * @throws InvalidKey
     * @throws InvalidMessage
     * @throws InvalidSalt
     * @throws InvalidType
     * @throws InvalidSignature
     */
    public function decrypt(string $encryptedMessage, string $password): string
    {
        $pieces = json_decode($encryptedMessage, true);

        $encryptionKey = KeyFactory::deriveEncryptionKey(new HiddenString($password), Hex::decode($pieces['salt']));

        return HaliteSymmetricCrypto::decrypt($pieces['message'], $encryptionKey)->getString();
    }
}
