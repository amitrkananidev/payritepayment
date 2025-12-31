@extends('new_layouts/app')

@section('title', 'Profile')

@section('page-style')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #667eea;
            --primary-dark: #5a67d8;
            --secondary: #764ba2;
            --accent: #f093fb;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --dark: #1f2937;
            --light: #f8fafc;
            --white: #ffffff;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-400: #94a3b8;
            --gray-500: #64748b;
            --gray-600: #475569;
            --gray-700: #334155;
            --gray-800: #1e293b;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: var(--gray-700);
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .profile-header {
            /*background: var(--white);*/
            border-radius: 24px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-xl);
            position: relative;
            overflow: hidden;
        }

        .profile-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 120px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            z-index: 1;
        }

        .profile-content {
            position: relative;
            z-index: 2;
            display: flex;
            align-items: flex-start;
            gap: 2rem;
            margin-top: 80px;
        }

        .profile-avatar {
            position: relative;
        }

        .avatar-container {
            position: relative;
            width: 160px;
            height: 160px;
            border-radius: 50%;
            border: 6px solid var(--white);
            overflow: hidden;
            box-shadow: var(--shadow-lg);
            background: var(--gray-100);
        }

        .avatar-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: var(--white);
            font-weight: 600;
        }

        .avatar-upload {
            position: absolute;
            bottom: 8px;
            right: 8px;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary);
            color: var(--white);
            border: 3px solid var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-md);
        }

        .avatar-upload:hover {
            background: var(--primary-dark);
            transform: scale(1.1);
        }

        .profile-info {
            flex: 1;
            color: var(--white);
            margin-top: 1rem;
        }

        .profile-name {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .profile-title {
            font-size: 1.25rem;
            opacity: 0.9;
            margin-bottom: 1rem;
        }

        .profile-meta {
            display: flex;
            gap: 2rem;
            margin-bottom: 1.5rem;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.95rem;
        }

        .profile-badges {
            display: flex;
            gap: 1rem;
        }

        .badge {
            padding: 0.5rem 1rem;
            border-radius: 25px;
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            font-size: 0.875rem;
            font-weight: 500;
        }

        .main-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .card {
            background: var(--white);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: var(--shadow-lg);
            transition: all 0.3s ease;
            border: 1px solid var(--gray-200);
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-xl);
        }

        .card-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .card-icon {
            width: 50px;
            height: 50px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: var(--white);
        }

        .card-icon.primary { background: linear-gradient(135deg, var(--primary), var(--primary-dark)); }
        .card-icon.success { background: linear-gradient(135deg, var(--success), #059669); }
        .card-icon.warning { background: linear-gradient(135deg, var(--warning), #d97706); }
        .card-icon.danger { background: linear-gradient(135deg, var(--danger), #dc2626); }

        .card-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--gray-800);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--gray-700);
        }

        .form-input {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid var(--gray-200);
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: var(--white);
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .btn {
            padding: 0.875rem 1.5rem;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            justify-content: center;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: var(--white);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success), #059669);
            color: var(--white);
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .btn-outline {
            background: transparent;
            border: 2px solid var(--gray-300);
            color: var(--gray-700);
        }

        .btn-outline:hover {
            background: var(--gray-50);
            border-color: var(--primary);
            color: var(--primary);
        }

        .download-section {
            grid-column: span 2;
        }

        .download-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }

        .download-card {
            background: var(--gray-50);
            border: 2px dashed var(--gray-300);
            border-radius: 16px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .download-card:hover {
            border-color: var(--primary);
            background: rgba(102, 126, 234, 0.05);
        }

        .download-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto 1rem;
        }

        .download-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--gray-800);
        }

        .download-desc {
            color: var(--gray-600);
            margin-bottom: 1.5rem;
        }

        .status-indicator {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .status-active {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .status-pending {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }

        .security-grid {
            display: grid;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .security-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            background: var(--gray-50);
            border-radius: 12px;
            border: 1px solid var(--gray-200);
        }

        .security-info h4 {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
            color: var(--gray-800);
        }

        .security-info p {
            color: var(--gray-600);
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .profile-content {
                flex-direction: column;
                align-items: center;
                text-align: center;
                margin-top: 60px;
            }

            .avatar-container {
                width: 120px;
                height: 120px;
            }

            .profile-name {
                font-size: 2rem;
            }

            .profile-meta {
                flex-direction: column;
                gap: 1rem;
            }

            .main-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .download-section {
                grid-column: span 1;
            }

            .form-row {
                grid-template-columns: 1fr;
            }
        }

        .file-input {
            display: none;
        }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1rem 1.5rem;
            background: var(--success);
            color: var(--white);
            border-radius: 12px;
            box-shadow: var(--shadow-lg);
            transform: translateX(400px);
            transition: transform 0.3s ease;
            z-index: 1000;
        }

        .notification.show {
            transform: translateX(0);
        }
    </style>
@endsection

@section('content')
<div class="content-wrapper">
    <!-- Profile Header -->
        <div class="profile-header">
            <div class="profile-content">
                <div class="profile-avatar">
                    <div class="avatar-container">
                        <div class="avatar-img" id="avatarDisplay">
                            <img src="{{ asset('shop.png') }}" alt="Profile" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                        </div>
                    </div>
                    <div class="avatar-upload">
                        <i class="fas fa-camera"></i>
                        <input type="file" class="file-input" id="avatarInput" accept="image/*">
                    </div>
                </div>
                <div class="profile-info">
                    <h1 class="profile-name">{{ Auth::user()->load('shopDetail')->shopDetail->shop_name }}</h1>
                    <p class="profile-title">{{ Auth::user()->name }} {{ Auth::user()->surname }}</p>
                    <div class="profile-meta">
                        <div class="meta-item">
                            <i class="fas fa-envelope"></i>
                            <span>{{ Auth::user()->email }}</span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-phone"></i>
                            <span>+91 {{ Auth::user()->mobile }}</span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span id="location_display">India</span>
                        </div>
                    </div>
                    <div class="profile-badges">
                        @if(Auth::user()->load('kycDocs')->kycDocs->status == 1)<span class="badge">Verified Account</span>@endif
                        <span class="badge">Premium Member</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="main-grid">
            <!-- Profile Details -->
            <div class="card">
                <div class="card-header">
                    <div class="card-icon primary">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3 class="card-title">Profile Details</h3>
                </div>
                <form id="profileForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">First Name</label>
                            <input type="text" class="form-input" value="{{ Auth::user()->name }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Last Name</label>
                            <input type="text" class="form-input" value="{{ Auth::user()->surname }}" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-input" value="{{ Auth::user()->email }}" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Mobile Number</label>
                            <input type="tel" class="form-input" value="{{ Auth::user()->mobile }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Department</label>
                            <input type="text" class="form-input" value="Finance">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Address</label>
                        <input type="text" class="form-input" value="{{ Auth::user()->load('shopDetail')->shopDetail->shop_address }}">
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Update Profile
                    </button>
                </form>
            </div>

            <!-- Password Change -->
            <div class="card">
                <div class="card-header">
                    <div class="card-icon danger">
                        <i class="fas fa-lock"></i>
                    </div>
                    <h3 class="card-title">Security Settings</h3>
                </div>
                <form id="passwordForm">
                    <div class="form-group">
                        <label class="form-label">Current Password</label>
                        <input type="password" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">New Password</label>
                        <input type="password" class="form-input" id="newPassword" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" class="form-input" id="confirmPassword" required>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-key"></i>
                        Change Password
                    </button>
                </form>

                <div class="security-grid">
                    <div class="security-item">
                        <div class="security-info">
                            <h4>Two-Factor Authentication</h4>
                            <p>Add extra security to your account</p>
                        </div>
                        <span class="status-indicator status-pending">
                            <i class="fas fa-clock"></i>
                            Disabled
                        </span>
                    </div>
                    <div class="security-item">
                        <div class="security-info">
                            <h4>Login Notifications</h4>
                            <p>Get notified of new sign-ins</p>
                        </div>
                        <span class="status-indicator status-active">
                            <i class="fas fa-check"></i>
                            Enabled
                        </span>
                    </div>
                </div>
            </div>

            <!-- Document Downloads -->
            <div class="card download-section">
                <div class="card-header">
                    <div class="card-icon success">
                        <i class="fas fa-download"></i>
                    </div>
                    <h3 class="card-title">Document Downloads</h3>
                </div>
                <p>Access and download your important documents and tax information</p>
                
                <div class="download-grid">
                    <div class="download-card" onclick="downloadDocument('tds')">
                        <div class="download-icon">
                            <i class="fas fa-receipt"></i>
                        </div>
                        <h4 class="download-title">TDS Certificate</h4>
                        <p class="download-desc">Download your Tax Deducted at Source certificate for the current financial year</p>
                        <button class="btn btn-success">
                            <i class="fas fa-file-pdf"></i>
                            Download TDS
                        </button>
                    </div>

                    <div class="download-card" onclick="downloadDocument('gst')">
                        <div class="download-icon">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                        <h4 class="download-title">GST Documents</h4>
                        <p class="download-desc">Access your GST registration certificate and related tax documents</p>
                        <button class="btn btn-success">
                            <i class="fas fa-file-pdf"></i>
                            Download GST
                        </button>
                    </div>

                    <div class="download-card" onclick="downloadDocument('salary')">
                        <div class="download-icon">
                            <i class="fas fa-money-check-alt"></i>
                        </div>
                        <h4 class="download-title">Salary Slips</h4>
                        <p class="download-desc">Download your monthly salary slips and annual salary statements</p>
                        <button class="btn btn-success">
                            <i class="fas fa-file-pdf"></i>
                            Download Salary
                        </button>
                    </div>

                    <div class="download-card" onclick="downloadDocument('form16')">
                        <div class="download-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <h4 class="download-title">Form 16</h4>
                        <p class="download-desc">Download your annual Form 16 for income tax filing purposes</p>
                        <button class="btn btn-success">
                            <i class="fas fa-file-pdf"></i>
                            Download Form 16
                        </button>
                    </div>
                </div>
            </div>
        </div>
</div>
@endsection

@section('page-script')
<script>
function getCityState(lat, lng) {
    const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`;
    
    $.ajax({
        url: url,
        type: 'GET',
        success: function(response) {
            //console.log(response);
            const city = response.address.city || 
                        response.address.town || 
                        response.address.village || '';
            const state = response.address.state || null;
            
            console.log('City:', city);
            console.log('State:', state);
            $("#location_display").text(city+' '+state)
        },
        error: function(error) {
            console.error('Error:', error);
        }
    });
}

// Usage
getCityState({{ Auth::user()->load('shopDetail')->shopDetail->latitude }}, {{ Auth::user()->load('shopDetail')->shopDetail->longitude }});
</script>
@endsection