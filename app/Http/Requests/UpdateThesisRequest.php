<?php

namespace App\Http\Requests;

/**
 * Validation for editing a thesis record. Reuses StoreThesisRequest's rules so
 * "what is a valid thesis" lives in exactly one place (coding standard #3).
 *
 * Ownership is enforced in the controller via ThesisPolicy (FR-3.4/3.6).
 */
class UpdateThesisRequest extends StoreThesisRequest {}
