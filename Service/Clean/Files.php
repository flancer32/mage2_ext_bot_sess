<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2018
 */

namespace Flancer32\BotSess\Service\Clean;

use Flancer32\BotSess\Service\Clean\Files\Request as ARequest;
use Flancer32\BotSess\Service\Clean\Files\Response as AResponse;

/**
 * Scan all session files and remove old ones w/o human activity.
 */
class Files
{
    /** @var \Magento\Framework\Filesystem */
    private $filesystem;
    /** @var \Flancer32\BotSess\Helper\Config */
    private $hlpConfig;
    /** @var \Magento\Framework\Session\SessionManagerInterface */
    private $sessionManager;

    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Flancer32\BotSess\Helper\Config $hlpConfig,
        \Magento\Framework\Session\SessionManagerInterface $sessionManager
    ) {
        $this->filesystem = $filesystem;
        $this->hlpConfig = $hlpConfig;
        $this->sessionManager = $sessionManager;
    }

    /**
     * Thanks to Jefersson Nathan <malukenho@phpse.net>
     * (https://github.com/psr7-sessions/session-encode-decode)
     *
     * @param string $encodedSessionData
     * @return array
     */
    private function decode(string $encodedSessionData): array
    {
        if ('' === $encodedSessionData) {
            return [];
        }
        preg_match_all('/(^|;|\})(\w+)\|/i', $encodedSessionData, $matchesarray, PREG_OFFSET_CAPTURE);
        $decodedData = [];
        $lastOffset = null;
        $currentKey = '';
        foreach ($matchesarray[2] as $value) {
            $offset = $value[1];
            if (null !== $lastOffset) {
                $valueText = substr($encodedSessionData, $lastOffset, $offset - $lastOffset);
                /** @noinspection UnserializeExploitsInspection */
                $decodedData[$currentKey] = unserialize($valueText);
            }
            $currentKey = $value[0];
            $lastOffset = $offset + strlen($currentKey) + 1;
        }
        $valueText = substr($encodedSessionData, $lastOffset);
        /** @noinspection UnserializeExploitsInspection */
        $decodedData[$currentKey] = unserialize($valueText);
        return $decodedData;
    }

    /**
     * @param \Flancer32\BotSess\Service\Clean\Files\Request $request
     * @return \Flancer32\BotSess\Service\Clean\Files\Response
     */
    public function exec($request)
    {
        assert($request instanceof ARequest);
        /* result data placeholders */
        $total = $removed = $skipped = $failures = 0;
        /* current time and max delta for session lifetime (in seconds)*/
        $now = time();
        $deltaMax = $this->hlpConfig->getBotsCleanupDelta();
        /* get all session files */
        $path = ini_get('session.save_path');
        $files = scandir($path);
        foreach ($files as $file) {
            try {
                $full = $path . DIRECTORY_SEPARATOR . $file;
                if (is_file($full)) {
                    $total++;
                    /* get date created and calculate delta (in seconds) */
                    $lastModified = filemtime($full);
                    $delta = $now - $lastModified;
                    if ($delta > $deltaMax) {
                        /* process session files that are older then required */
                        $content = file_get_contents($full);
                        $decoded = $this->decode($content);
                        if (
                            is_array($decoded) &&
                            isset($decoded['_session_validator_data'])
                        ) {
                            /* Magento session is expected */
                            if (
                                isset($decoded['catalog']) &&
                                is_array($decoded['catalog']) &&
                                (count($decoded['catalog']) > 0) &&
                                isset($decoded['checkout']) &&
                                is_array($decoded['checkout']) &&
                                (count($decoded['checkout']) > 0)
                            ) {
                                /* there are not empty catalog & checkout sections in the session */
                                $skipped++;
                            } else {
                                /* probably it is a bot's session, remove it */
                                if (unlink($full)) {
                                    $removed++;
                                } else {
                                    $failures++;
                                }
                            }
                        } else {
                            /* is not a magento session */
                            $skipped++;
                        }
                    } else {
                        /* this session is not so old yet */
                        $skipped++;
                    }
                }
            } catch (\Throwable $e) {
                $msg = $e->getMessage();
                $failures++;
            }
        }
        /** compose result */
        $result = new AResponse();
        $result->failures = $failures;
        $result->removed = $removed;
        $result->skipped = $skipped;
        $result->total = $total;
        return $result;
    }
}