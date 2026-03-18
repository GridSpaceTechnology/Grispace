<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'tagline' => $this->tagline,
            'description' => $this->description,
            'industry' => $this->industry,
            'company_size' => $this->company_size,
            'founded_year' => $this->founded_year,
            'website' => $this->website,
            'logo_url' => $this->logo_url,
            'cover_image_url' => $this->cover_image_url,
            'location' => $this->location,
            'location_country' => $this->location_country,
            'culture_values' => $this->culture_values_json,
            'benefits' => $this->benefits_json,
            'is_verified' => $this->is_verified,
        ];
    }
}
