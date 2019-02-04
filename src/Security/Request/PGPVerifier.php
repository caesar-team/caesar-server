<?php

declare(strict_types=1);

namespace App\Security\Request;

use Symfony\Component\HttpFoundation\Request;

class PGPVerifier implements RequestVerifierInterface
{
    private const SIGNATURE_HEADER = 'X-Signature';

    public function verifyRequest(Request $request, string $publicKey): bool
    {
        $signature = $this->normalizeSignature($request->headers->get(self::SIGNATURE_HEADER, ''));

        $gpg = new \gnupg();
        $public = $gpg->import($publicKey);
        $verify = $gpg->verify($this->getSignedTextRequest($request), $signature);
        if (!is_array($verify)) {
            return false;
        }
        $verify = current($verify);

        return $verify['fingerprint'] === $public['fingerprint'];
    }

    private function getSignedTextRequest(Request $request): string
    {
        return sprintf('%s-%s', $request->getRequestUri(), json_encode($request->request->all()));
    }

    private function normalizeSignature(string $signature): string
    {
        $signature = str_replace(['-----BEGIN PGP SIGNATURE-----', '-----END PGP SIGNATURE-----', PHP_EOL], '', $signature);

        return sprintf(
            '-----BEGIN PGP SIGNATURE-----%s%s%s-----END PGP SIGNATURE-----',
            PHP_EOL,
            $signature,
            PHP_EOL
        );
    }
}
