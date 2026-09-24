<?php

namespace App\Http\Controllers;

use App\Models\Barangay;
use App\Models\BarangayContact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BarangayContactController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->value();
        $barangayId = $request->integer('barangay_id') ?: null;

        $contacts = BarangayContact::query()
            ->with('barangay')
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('role', 'like', "%{$search}%")
                        ->orWhere('phone_number', 'like', "%{$search}%");
                });
            })
            ->when($barangayId !== null, fn ($q) => $q->where('barangay_id', $barangayId))
            ->orderBy('name')
            ->get()
            ->map(fn (BarangayContact $contact) => $this->toRow($contact));

        $barangays = Barangay::query()
            ->orderBy('barangay_name')
            ->get(['barangay_id', 'barangay_name']);

        return Inertia::render('barangay-contacts/index', [
            'contacts' => $contacts,
            'barangays' => $barangays,
            'filters' => [
                'search' => $search,
                'barangay_id' => $barangayId,
            ],
            'totalCount' => BarangayContact::count(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'barangay_id' => ['required', 'integer', 'exists:barangay,barangay_id'],
            'name' => ['required', 'string', 'max:100'],
            'role' => ['required', 'string', 'max:50'],
            'phone_number' => ['required', 'string', 'max:20'],
        ]);

        $contact = BarangayContact::create($data);

        return back()->with('toast', [
            'type' => 'success',
            'message' => "{$contact->name} was added to Barangay Contacts.",
        ]);
    }

    public function update(Request $request, BarangayContact $barangayContact): RedirectResponse
    {
        $data = $request->validate([
            'barangay_id' => ['required', 'integer', 'exists:barangay,barangay_id'],
            'name' => ['required', 'string', 'max:100'],
            'role' => ['required', 'string', 'max:50'],
            'phone_number' => ['required', 'string', 'max:20'],
        ]);

        $barangayContact->update($data);

        return back()->with('toast', [
            'type' => 'success',
            'message' => "{$barangayContact->name}'s contact was updated.",
        ]);
    }

    public function destroy(BarangayContact $barangayContact): RedirectResponse
    {
        $name = $barangayContact->name;
        $barangayContact->delete();

        return back()->with('toast', [
            'type' => 'success',
            'message' => "{$name} was removed from Barangay Contacts.",
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function toRow(BarangayContact $contact): array
    {
        return [
            'id' => $contact->contact_id,
            'barangay_id' => $contact->barangay_id,
            'barangay_name' => $contact->barangay?->barangay_name,
            'name' => $contact->name,
            'role' => $contact->role,
            'phone_number' => $contact->phone_number,
        ];
    }
}
