<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $roleName = $this->getRoleNames()->first();
        $rolesArabic = [
            'admin'         => 'مدير المنصة',
            'lab_manager'   => 'مدير مختبر',
            'lab_assistant' => 'فني مختبر',
            'receptionist'  => 'موظف استقبال',
            'patient'       => 'مريض',
        ];
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'avatar' => $this->avatar ?? 'default-avatar.png',
            'role' => $roleName,
            'role_display' => $rolesArabic[$roleName] ?? 'مستخدم',
            'status' => $this->status, 
            'registration_date' => $this->created_at->format('d أكتوبر Y'), 
            'last_seen_for_humans' => $this->last_seen_at ? $this->last_seen_at->diffForHumans() : 'لم يظهر أبداً',
            'is_online' => $this->last_seen_at ? $this->last_seen_at->diffInMinutes(now()) < 5 : false,
        ];
    }
}
