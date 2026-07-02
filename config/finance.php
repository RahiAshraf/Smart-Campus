<?php

function finance_ensure_tables(mysqli $conn): void
{
    mysqli_query($conn, "
        CREATE TABLE IF NOT EXISTS teacher_salaries (
            salary_id INT(11) NOT NULL AUTO_INCREMENT,
            teacher_id INT(11) NOT NULL,
            monthly_salary DECIMAL(10,2) NOT NULL,
            effective_from DATE NOT NULL,
            status ENUM('active','inactive') NOT NULL DEFAULT 'active',
            notes TEXT DEFAULT NULL,
            created_by INT(11) DEFAULT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (salary_id),
            KEY idx_teacher_salaries_teacher (teacher_id),
            KEY idx_teacher_salaries_status (status),
            KEY idx_teacher_salaries_created_by (created_by)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    mysqli_query($conn, "
        CREATE TABLE IF NOT EXISTS salary_payments (
            payment_id INT(11) NOT NULL AUTO_INCREMENT,
            receipt_no VARCHAR(50) DEFAULT NULL,
            salary_id INT(11) NOT NULL,
            teacher_id INT(11) NOT NULL,
            pay_period VARCHAR(20) NOT NULL,
            amount_paid DECIMAL(10,2) NOT NULL,
            payment_date DATE NOT NULL,
            payment_method ENUM('cash','upi','card','bank_transfer','cheque') DEFAULT 'cash',
            transaction_ref VARCHAR(100) DEFAULT NULL,
            remarks VARCHAR(255) DEFAULT NULL,
            created_by INT(11) DEFAULT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (payment_id),
            KEY idx_salary_payments_salary (salary_id),
            KEY idx_salary_payments_teacher (teacher_id),
            KEY idx_salary_payments_date (payment_date),
            KEY idx_salary_payments_period (pay_period),
            KEY idx_salary_payments_created_by (created_by)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
}

function finance_money($amount): string
{
    return '₹' . number_format((float) $amount, 2);
}

function finance_payment_methods(): array
{
    return [
        'cash' => 'Cash',
        'upi' => 'UPI',
        'card' => 'Card',
        'bank_transfer' => 'Bank Transfer',
        'cheque' => 'Cheque',
    ];
}
