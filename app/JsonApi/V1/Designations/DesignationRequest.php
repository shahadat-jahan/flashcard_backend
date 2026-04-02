<?php

namespace App\JsonApi\V1\Designations;

use App\Enums\DesignationStatus;
use Illuminate\Validation\Rule;
use LaravelJsonApi\Laravel\Http\Requests\ResourceRequest;

class DesignationRequest extends ResourceRequest
{
    /**
     * Get the validation rules for the resource.
     */
    public function rules(): array
    {
        $designation = $this->model();
        $uniqueDesignationName = Rule::unique('designations', 'name');
        if ($designation) {
            $uniqueDesignationName->ignoreModel($designation);
        }

        return [
            'name' => ['required', 'string', 'max:100', $uniqueDesignationName],
            'status' => ['nullable', 'integer', Rule::enum(DesignationStatus::class)],
        ];
    }
}
