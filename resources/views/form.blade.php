@if ($errors->any())
    <div class="notice notice-error" role="alert">
        <strong>Please check the following:</strong>
        <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<section class="card form-card" aria-labelledby="account-information">
    <div class="form-section">
        <div class="section-title"><h2 id="account-information">Account information</h2><p>Enter the employee's personal and login details.</p></div>
        <div class="form-grid">
            <div class="field">
                <label for="name">Full name <span aria-hidden="true">*</span></label>
                <input id="name" name="name" type="text" value="{{ old('name', $employee->name ?? '') }}" maxlength="255" autocomplete="name" required @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
                @error('name') <span class="field-error" id="name-error">{{ $message }}</span> @enderror
            </div>
            <div class="field">
                <label for="email">Email address <span aria-hidden="true">*</span></label>
                <input id="email" name="email" type="email" value="{{ old('email', $employee->email ?? '') }}" maxlength="255" autocomplete="email" required @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                @error('email') <span class="field-error" id="email-error">{{ $message }}</span> @enderror
            </div>
            <div class="field">
                <label for="phone">Phone number <span class="optional">Optional</span></label>
                <input id="phone" name="phone" type="tel" value="{{ old('phone', $employee->phone ?? '') }}" maxlength="20" autocomplete="tel" @error('phone') aria-invalid="true" aria-describedby="phone-error" @enderror>
                @error('phone') <span class="field-error" id="phone-error">{{ $message }}</span> @enderror
            </div>
            <div class="field">
                <label for="role">Role <span aria-hidden="true">*</span></label>
                <select id="role" name="role" required @error('role') aria-invalid="true" aria-describedby="role-error" @enderror>
                    <option value="employee" @selected(old('role', $employee->role ?? 'employee') === 'employee')>Employee</option>
                    <option value="admin" @selected(old('role', $employee->role ?? 'employee') === 'admin')>Admin</option>
                </select>
                @error('role') <span class="field-error" id="role-error">{{ $message }}</span> @enderror
            </div>
            <div class="field">
                <label for="password">{{ $editing ? 'New password' : 'Password' }} @if ($editing)<span class="optional">Optional</span>@else<span aria-hidden="true">*</span>@endif</label>
                <input id="password" name="password" type="password" autocomplete="new-password" {{ $editing ? '' : 'required' }} @error('password') aria-invalid="true" aria-describedby="password-error" @enderror>
                @if ($editing)<span class="field-hint">Leave blank to keep the current password.</span>@endif
                @error('password') <span class="field-error" id="password-error">{{ $message }}</span> @enderror
            </div>
        </div>
    </div>
    <div class="form-section">
        <div class="section-title"><h2>Employment information</h2><p>Add the employee's position and compensation details.</p></div>
        <div class="form-grid">
            <div class="field">
                <label for="department_id">Department ID <span class="optional">Optional</span></label>
                <input id="department_id" name="department_id" type="number" min="1" step="1" value="{{ old('department_id', $employee->department_id ?? '') }}" @error('department_id') aria-invalid="true" aria-describedby="department-error" @enderror>
                @error('department_id') <span class="field-error" id="department-error">{{ $message }}</span> @enderror
            </div>
            <div class="field">
                <label for="designation_id">Designation ID <span class="optional">Optional</span></label>
                <input id="designation_id" name="designation_id" type="number" min="1" step="1" value="{{ old('designation_id', $employee->designation_id ?? '') }}" @error('designation_id') aria-invalid="true" aria-describedby="designation-error" @enderror>
                @error('designation_id') <span class="field-error" id="designation-error">{{ $message }}</span> @enderror
            </div>
            <div class="field">
                <label for="salary">Salary <span aria-hidden="true">*</span></label>
                <input id="salary" name="salary" type="number" min="0" max="99999999.99" step="0.01" value="{{ old('salary', $employee->salary ?? '') }}" required @error('salary') aria-invalid="true" aria-describedby="salary-error" @enderror>
                @error('salary') <span class="field-error" id="salary-error">{{ $message }}</span> @enderror
            </div>
        </div>
    </div>
    <div class="form-actions">
        <a class="button button-outline" href="{{ $editing ? url('/employees/' . $employee->emp_id) : url('/employees') }}">Cancel</a>
        <button class="button button-primary" type="submit">{{ $editing ? 'Save changes' : 'Create employee' }}</button>
    </div>
</section>
