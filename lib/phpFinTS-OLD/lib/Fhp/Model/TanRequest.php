<?php

namespace Fhp\Model;

use Fhp\Syntax\Bin;

/**
 * Provides information that can be used to display a TAN request to the user, plus identifiers to track the TAN request
 * and match the TAN to the request once the user entered it. Note that some additional information (e.g. about the TAN
 * format) can be obtained from the {@link TanMode} that was selected beforehand.
 */
interface TanRequest
{
    /**
     * @return string An identifier used by the bank to match the provided TAN with the original request.
     */
    public function getProcessId(): string;

    /**
     * @return string A challenge to be displayed to the user.
     */
    public function getChallenge(): string;

    /**
     * @return string|null Possibly the name of the {@link TanMedium} to be used. If present, this should be displayed
     *     to the user, so that they know what to do.
     */
    public function getTanMediumName(): ?string;

    /**
     * @return Bin|null An additional binary challenge payload. Used to receive the PhotoTan/ChipTan image. Use TanRequestChallengeImage to parse the binary.
     */
    public function getChallengeHhdUc(): ?Bin;
}
