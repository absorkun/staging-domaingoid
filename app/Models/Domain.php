<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Domain extends Model
{
    protected $fillable = [
        'name',
        'zone',
        'dns_sec',
        'domain_name_server',
        'ip_address',
        'status_code',
    ];

    protected $casts = [
        'dns_sec' => 'boolean',
        'domain_name_server' => 'array',
        'ip_address' => 'array',
    ];

    public function getHostnameAttribute(): string
    {
        return $this->name . $this->zone;
    }
}
