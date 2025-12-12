<?php

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['user']) || !isset($input['role'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$user = $input['user'];
$role = strtolower($input['role']);
$remember = isset($input['remember']) && $input['remember'] == true;

// Konfigurasi cookie lifetime 
if ($remember) {
    $lifetime = 30 * 24 * 60 * 60; // 30 hari (Remember me)
    session_set_cookie_params([
        'lifetime' => $lifetime,
        'path' => '/',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
} else {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}

session_start();
session_regenerate_id(true);

// session student
if ($role === 'student') {
    $_SESSION['student'] = [
        'nim' => $user['nim'] ?? null,
        'username' => $user['username'] ?? null,
        'name' => $user['name'] ?? null,
        'email' => $user['email'] ?? null,
        'program_study' => $user['program_study'] ?? null,
        'other_email' => $user['other_email'] ?? null,
        'phone' => $user['phone'] ?? null,
        'no_whatsapp' => $user['no_whatsapp'] ?? null,
        'profile_picture' => $user['profile_picture'] ?? null,
        'nik_dospem' => $user['nik_dospem'] ?? null,
        'id_kampus' => $user['id_kampus'] ?? null,
        'account_status' => $user['account_status'] ?? null
    ];
    echo json_encode(['success' => true, 'message' => 'Student session created']);
    exit;
}

// Session lecturer
if ($role === 'lecturer') {
    $_SESSION['lecturer'] = [
        'nim_nik_unit' => $user['nim_nik_unit'] ?? null,
        'name' => $user['name'] ?? null,
        'email_polibatam' => $user['email_polibatam'] ?? null,
        'role' => $user['role'] ?? 'lecturer',
        'is_koor' => $user['is_koor'] ?? 0,
        'prodi_koor' => $user['prodi_koor'] ?? null,
        'status' => $user['status'] ?? null,
        'id_kampus' => $user['id_kampus'] ?? null
    ];
    echo json_encode(['success' => true, 'message' => 'Lecturer session created']);
    exit;
}

// Session cdc
if ($role === 'cdc') {
    $_SESSION['cdc'] = [
        'id_upkpk' => $user['id'] ?? null,
        'username' => $user['username'] ?? null,
        'name' => $user['name'] ?? null,
        'profile_picture' => $user['profile_picture'] ?? null,
        'id_kampus' => $user['id_kampus'] ?? null
    ];
    echo json_encode(['success' => true, 'message' => 'CDC session created']);
    exit;
}

// Role tidak valid
http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Invalid role']);
exit;
?>