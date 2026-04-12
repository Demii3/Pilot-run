-- Payroll System Database Schema for MySQL
-- Comprehensive schema with proper indexing and constraints

-- 1. Create Cost Centers table
CREATE TABLE cost_centers (
  cost_center_id INT PRIMARY KEY AUTO_INCREMENT,
  code VARCHAR(20) NOT NULL UNIQUE,
  name VARCHAR(100) NOT NULL,
  description TEXT,
  status ENUM('active', 'inactive') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_code (code),
  INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Create Departments table
CREATE TABLE departments (
  dept_id INT PRIMARY KEY AUTO_INCREMENT,
  code VARCHAR(20) NOT NULL UNIQUE,
  name VARCHAR(100) NOT NULL,
  cost_center_id INT NOT NULL,
  description TEXT,
  status ENUM('active', 'inactive') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (cost_center_id) REFERENCES cost_centers(cost_center_id),
  INDEX idx_code (code),
  INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Create Positions table
CREATE TABLE positions (
  position_id INT PRIMARY KEY AUTO_INCREMENT,
  code VARCHAR(20) NOT NULL UNIQUE,
  title VARCHAR(100) NOT NULL,
  description TEXT,
  status ENUM('active', 'inactive') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_code (code),
  INDEX idx_title (title)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Create Pay Grades table
CREATE TABLE pay_grades (
  pay_grade_id INT PRIMARY KEY AUTO_INCREMENT,
  code VARCHAR(20) NOT NULL UNIQUE,
  name VARCHAR(100) NOT NULL,
  min_salary DECIMAL(12, 2) NOT NULL,
  max_salary DECIMAL(12, 2) NOT NULL,
  description TEXT,
  status ENUM('active', 'inactive') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_code (code),
  INDEX idx_salary_range (min_salary, max_salary)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Create Employees table
CREATE TABLE employees (
  emp_id INT PRIMARY KEY AUTO_INCREMENT,
  emp_code VARCHAR(20) NOT NULL UNIQUE,
  first_name VARCHAR(50) NOT NULL,
  last_name VARCHAR(50) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  phone VARCHAR(20),
  date_of_birth DATE,
  gender ENUM('Male', 'Female', 'Other'),
  marital_status ENUM('Single', 'Married', 'Divorced', 'Widowed'),
  nationality VARCHAR(50),
  address TEXT,
  city VARCHAR(50),
  state VARCHAR(50),
  postal_code VARCHAR(20),
  country VARCHAR(50),
  dept_id INT NOT NULL,
  position_id INT NOT NULL,
  employment_date DATE NOT NULL,
  employment_status ENUM('active', 'on_leave', 'suspended', 'terminated') DEFAULT 'active',
  termination_date DATE,
  bank_account_number VARCHAR(50),
  bank_name VARCHAR(100),
  pan_number VARCHAR(20),
  aadhar_number VARCHAR(20),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (dept_id) REFERENCES departments(dept_id),
  FOREIGN KEY (position_id) REFERENCES positions(position_id),
  INDEX idx_emp_code (emp_code),
  INDEX idx_email (email),
  INDEX idx_dept_id (dept_id),
  INDEX idx_status (employment_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Create Salary Structure table
CREATE TABLE salary_structures (
  salary_structure_id INT PRIMARY KEY AUTO_INCREMENT,
  emp_id INT NOT NULL,
  pay_grade_id INT NOT NULL,
  base_salary DECIMAL(12, 2) NOT NULL,
  effective_date DATE NOT NULL,
  end_date DATE,
  status ENUM('active', 'inactive') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (emp_id) REFERENCES employees(emp_id),
  FOREIGN KEY (pay_grade_id) REFERENCES pay_grades(pay_grade_id),
  UNIQUE KEY unique_active_salary (emp_id, effective_date),
  INDEX idx_emp_id (emp_id),
  INDEX idx_effective_date (effective_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Create Allowances table
CREATE TABLE allowances (
  allowance_id INT PRIMARY KEY AUTO_INCREMENT,
  code VARCHAR(20) NOT NULL UNIQUE,
  name VARCHAR(100) NOT NULL,
  type ENUM('fixed', 'percentage', 'variable') DEFAULT 'fixed',
  description TEXT,
  status ENUM('active', 'inactive') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_code (code),
  INDEX idx_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Create Employee Allowances mapping
CREATE TABLE employee_allowances (
  emp_allowance_id INT PRIMARY KEY AUTO_INCREMENT,
  emp_id INT NOT NULL,
  allowance_id INT NOT NULL,
  amount DECIMAL(12, 2) NOT NULL,
  effective_date DATE NOT NULL,
  end_date DATE,
  status ENUM('active', 'inactive') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (emp_id) REFERENCES employees(emp_id),
  FOREIGN KEY (allowance_id) REFERENCES allowances(allowance_id),
  INDEX idx_emp_id (emp_id),
  INDEX idx_effective_date (effective_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Create Deductions table
CREATE TABLE deductions (
  deduction_id INT PRIMARY KEY AUTO_INCREMENT,
  code VARCHAR(20) NOT NULL UNIQUE,
  name VARCHAR(100) NOT NULL,
  type ENUM('tax', 'insurance', 'loan', 'advance', 'other') DEFAULT 'other',
  description TEXT,
  status ENUM('active', 'inactive') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_code (code),
  INDEX idx_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Create Employee Deductions mapping
CREATE TABLE employee_deductions (
  emp_deduction_id INT PRIMARY KEY AUTO_INCREMENT,
  emp_id INT NOT NULL,
  deduction_id INT NOT NULL,
  amount DECIMAL(12, 2) NOT NULL,
  effective_date DATE NOT NULL,
  end_date DATE,
  status ENUM('active', 'inactive') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (emp_id) REFERENCES employees(emp_id),
  FOREIGN KEY (deduction_id) REFERENCES deductions(deduction_id),
  INDEX idx_emp_id (emp_id),
  INDEX idx_effective_date (effective_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Create Tax Brackets table
CREATE TABLE tax_brackets (
  tax_bracket_id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  financial_year VARCHAR(9) NOT NULL,
  min_salary DECIMAL(12, 2) NOT NULL,
  max_salary DECIMAL(12, 2),
  tax_rate DECIMAL(5, 2) NOT NULL,
  description TEXT,
  status ENUM('active', 'inactive') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY unique_bracket (financial_year, min_salary),
  INDEX idx_financial_year (financial_year),
  INDEX idx_salary_range (min_salary, max_salary)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Create Employee Tax Info table
CREATE TABLE employee_tax_info (
  emp_tax_id INT PRIMARY KEY AUTO_INCREMENT,
  emp_id INT NOT NULL,
  financial_year VARCHAR(9) NOT NULL,
  tax_bracket_id INT,
  annual_ctc DECIMAL(12, 2),
  total_tax DECIMAL(12, 2) DEFAULT 0,
  tax_status ENUM('calculated', 'pending', 'verified') DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (emp_id) REFERENCES employees(emp_id),
  FOREIGN KEY (tax_bracket_id) REFERENCES tax_brackets(tax_bracket_id),
  UNIQUE KEY unique_emp_year (emp_id, financial_year),
  INDEX idx_financial_year (financial_year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. Create Attendance table
CREATE TABLE attendance (
  attendance_id INT PRIMARY KEY AUTO_INCREMENT,
  emp_id INT NOT NULL,
  attendance_date DATE NOT NULL,
  status ENUM('present', 'absent', 'half_day', 'leave', 'weekend', 'holiday') DEFAULT 'absent',
  hours_worked DECIMAL(4, 2),
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (emp_id) REFERENCES employees(emp_id),
  UNIQUE KEY unique_attendance (emp_id, attendance_date),
  INDEX idx_emp_id (emp_id),
  INDEX idx_date (attendance_date),
  INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. Create Leave Types table
CREATE TABLE leave_types (
  leave_type_id INT PRIMARY KEY AUTO_INCREMENT,
  code VARCHAR(20) NOT NULL UNIQUE,
  name VARCHAR(100) NOT NULL,
  max_days INT NOT NULL,
  carry_forward_days INT DEFAULT 0,
  description TEXT,
  status ENUM('active', 'inactive') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. Create Leave Balance table
CREATE TABLE leave_balances (
  leave_balance_id INT PRIMARY KEY AUTO_INCREMENT,
  emp_id INT NOT NULL,
  leave_type_id INT NOT NULL,
  financial_year VARCHAR(9) NOT NULL,
  opening_balance INT DEFAULT 0,
  allocated_days INT NOT NULL,
  used_days INT DEFAULT 0,
  pending_approval_days INT DEFAULT 0,
  closing_balance INT GENERATED ALWAYS AS (allocated_days + opening_balance - used_days - pending_approval_days) STORED,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (emp_id) REFERENCES employees(emp_id),
  FOREIGN KEY (leave_type_id) REFERENCES leave_types(leave_type_id),
  UNIQUE KEY unique_leave_balance (emp_id, leave_type_id, financial_year),
  INDEX idx_emp_id (emp_id),
  INDEX idx_financial_year (financial_year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 16. Create Payroll table
CREATE TABLE payroll (
  payroll_id INT PRIMARY KEY AUTO_INCREMENT,
  payroll_period_start DATE NOT NULL,
  payroll_period_end DATE NOT NULL,
  financial_year VARCHAR(9) NOT NULL,
  status ENUM('draft', 'submitted', 'approved', 'processed', 'paid') DEFAULT 'draft',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY unique_period (payroll_period_start, payroll_period_end),
  INDEX idx_financial_year (financial_year),
  INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 17. Create Payroll Details table
CREATE TABLE payroll_details (
  payroll_detail_id INT PRIMARY KEY AUTO_INCREMENT,
  payroll_id INT NOT NULL,
  emp_id INT NOT NULL,
  base_salary DECIMAL(12, 2) NOT NULL,
  total_allowances DECIMAL(12, 2) DEFAULT 0,
  total_deductions DECIMAL(12, 2) DEFAULT 0,
  gross_salary DECIMAL(12, 2) GENERATED ALWAYS AS (base_salary + total_allowances) STORED,
  net_salary DECIMAL(12, 2) GENERATED ALWAYS AS (gross_salary - total_deductions) STORED,
  payment_status ENUM('pending', 'processed', 'paid') DEFAULT 'pending',
  payment_date DATE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (payroll_id) REFERENCES payroll(payroll_id),
  FOREIGN KEY (emp_id) REFERENCES employees(emp_id),
  UNIQUE KEY unique_payroll_emp (payroll_id, emp_id),
  INDEX idx_emp_id (emp_id),
  INDEX idx_payment_status (payment_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 18. Create Users table for system access
CREATE TABLE users (
  user_id INT PRIMARY KEY AUTO_INCREMENT,
  emp_id INT,
  username VARCHAR(50) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  role ENUM('admin', 'hr', 'manager', 'employee') DEFAULT 'employee',
  status ENUM('active', 'inactive', 'locked') DEFAULT 'active',
  last_login TIMESTAMP,
  failed_login_attempts INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (emp_id) REFERENCES employees(emp_id),
  INDEX idx_username (username),
  INDEX idx_email (email),
  INDEX idx_role (role),
  INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create views for common queries

-- View: Monthly Salary Summary
CREATE VIEW v_monthly_salary_summary AS
SELECT 
  pd.payroll_detail_id,
  p.payroll_id,
  p.payroll_period_start,
  p.payroll_period_end,
  e.emp_id,
  e.emp_code,
  CONCAT(e.first_name, ' ', e.last_name) AS employee_name,
  d.name AS department,
  pd.base_salary,
  pd.total_allowances,
  pd.total_deductions,
  pd.gross_salary,
  pd.net_salary,
  pd.payment_status
FROM payroll_details pd
JOIN payroll p ON pd.payroll_id = p.payroll_id
JOIN employees e ON pd.emp_id = e.emp_id
JOIN departments d ON e.dept_id = d.dept_id;

-- View: Employee Leave Balance
CREATE VIEW v_employee_leave_balance AS
SELECT 
  lb.leave_balance_id,
  e.emp_id,
  e.emp_code,
  CONCAT(e.first_name, ' ', e.last_name) AS employee_name,
  lt.name AS leave_type,
  lb.financial_year,
  lb.allocated_days,
  lb.used_days,
  lb.pending_approval_days,
  lb.closing_balance
FROM leave_balances lb
JOIN employees e ON lb.emp_id = e.emp_id
JOIN leave_types lt ON lb.leave_type_id = lt.leave_type_id;

-- View: Department Payroll Summary
CREATE VIEW v_department_payroll_summary AS
SELECT 
  d.dept_id,
  d.code,
  d.name AS department_name,
  p.payroll_period_start,
  p.payroll_period_end,
  COUNT(DISTINCT pd.emp_id) AS employee_count,
  SUM(pd.base_salary) AS total_base_salary,
  SUM(pd.total_allowances) AS total_allowances,
  SUM(pd.total_deductions) AS total_deductions,
  SUM(pd.gross_salary) AS total_gross_salary,
  SUM(pd.net_salary) AS total_net_salary
FROM payroll p
JOIN payroll_details pd ON p.payroll_id = pd.payroll_id
JOIN employees e ON pd.emp_id = e.emp_id
JOIN departments d ON e.dept_id = d.dept_id
GROUP BY d.dept_id, d.code, d.name, p.payroll_period_start, p.payroll_period_end;
