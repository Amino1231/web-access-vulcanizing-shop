
<div class="update-shell">
    <div class="topbar">
        <div>
            <p class="eyebrow">Settings</p>
            <h1>Update Admin Profile</h1>
        </div>
        <a href="{{ route('admin.profile') }}" class="secondary-btn">Back to profile</a>
    </div>

    <form wire:submit.prevent="save" class="form-panel">
        @if (session()->has('status'))
            <div class="success-box">{{ session('status') }}</div>
        @endif

        <div class="field-grid">
            <div class="field">
                <label for="name">Full name</label>
                <input id="name" type="text" wire:model="name" placeholder="Super Admin">
                @error('name') <span class="error">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label for="email">Email</label>
                <input id="email" type="email" wire:model="email" placeholder="admin@vulcanizepro.com">
                @error('email') <span class="error">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label for="phone">Phone</label>
                <input id="phone" type="text" wire:model="phone" placeholder="09000000000">
                @error('phone') <span class="error">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label for="location">Location</label>
                <input id="location" type="text" wire:model="location" placeholder="Metro District HQ">
                @error('location') <span class="error">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="actions">
            <button type="submit" class="primary-btn">Save changes</button>
        </div>
    </form>
</div>

<style>
    .update-shell {
        padding: 32px;
        background: #f5f7fb;
        min-height: 100vh;
        font-family: Arial, Helvetica, sans-serif;
        color: #111827;
    }

    .topbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin-bottom: 26px;
    }

    .eyebrow {
        margin: 0 0 8px;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #6b7280;
        font-size: 11px;
        font-weight: 700;
    }

    h1, p { margin: 0; }
    h1 { font-size: clamp(1.8rem, 2vw, 2.5rem); }

    .secondary-btn, .primary-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 0;
        border-radius: 12px;
        padding: 0.8rem 1.1rem;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
    }

    .secondary-btn {
        background: white;
        border: 1px solid #e5e7eb;
        color: #111827;
    }

    .primary-btn {
        background: linear-gradient(135deg, #ff7b2c, #ff9f43);
        color: white;
    }

    .form-panel {
        background: white;
        border: 1px solid #edf0f5;
        border-radius: 18px;
        padding: 24px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04);
    }

    .success-box {
        background: #edfff4;
        color: #0f766e;
        border: 1px solid #bcead5;
        border-radius: 10px;
        padding: 12px 14px;
        margin-bottom: 16px;
        font-weight: 600;
    }

    .field-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
    }

    .field {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    label {
        font-weight: 700;
        color: #374151;
    }

    input {
        width: 100%;
        border: 1px solid #dfe3ea;
        border-radius: 10px;
        padding: 0.85rem 0.9rem;
        font-size: 0.98rem;
        background: #fff;
        color: #111827;
    }

    input:focus {
        outline: none;
        border-color: #ff9f43;
        box-shadow: 0 0 0 3px rgba(255, 123, 44, 0.15);
    }

    .error {
        color: #d92d20;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .actions {
        margin-top: 24px;
        display: flex;
        justify-content: flex-end;
    }

    @media (max-width: 720px) {
        .update-shell {
            padding: 20px;
        }

        .topbar,
        .field-grid {
            display: grid;
            grid-template-columns: 1fr;
        }
    }
</style>