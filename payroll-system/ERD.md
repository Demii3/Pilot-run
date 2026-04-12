```mermaid
 erDiagram
    Employee {  
        int id PK "Primary Key"  
        string name  
        string position  
        string department  
        float salary  
    }  
    Department {  
        int id PK "Primary Key"  
        string name  
    }  
    Payroll {  
        int id PK "Primary Key"  
        int employeeId FK "Foreign Key"
        float amount  
        date paymentDate  
    }  
    Timesheet {  
        int id PK "Primary Key"  
        int employeeId FK "Foreign Key"  
        date workDate  
        float hoursWorked  
    }  
    Leave {  
        int id PK "Primary Key"  
        int employeeId FK "Foreign Key"  
        date startDate  
        date endDate  
        string reason  
    }  
    Benefit {  
        int id PK "Primary Key"  
        int employeeId FK "Foreign Key"  
        string type  
        float amount  
    }  
    Deduction {  
        int id PK "Primary Key"  
        int employeeId FK "Foreign Key"  
        string type  
        float amount  
    }  
    Overtime {  
        int id PK "Primary Key"  
        int employeeId FK "Foreign Key"  
        date workDate  
        float hours  
        float rate  
    }  
    Tax {  
        int id PK "Primary Key"  
        int employeeId FK "Foreign Key"  
        float amount  
        string type  
    }  
    PaymentHistory {  
        int id PK "Primary Key"  
        int payrollId FK "Foreign Key"  
        date paymentDate  
        float amount  
    }  
    EmployeeBenefits {  
        int id PK "Primary Key"  
        int employeeId FK "Foreign Key"  
        string benefitType  
        float amount  
    }  
    EmployeeLeave {  
        int id PK "Primary Key"  
        int employeeId FK "Foreign Key"  
        string leaveType  
        date startDate  
        date endDate  
    }  
    PayGrade {  
        int id PK "Primary Key"  
        string grade  
        float minSalary  
        float maxSalary  
    }  
    Training {  
        int id PK "Primary Key"  
        int employeeId FK "Foreign Key"  
        string trainingType  
        date trainingDate  
    }  
    PerformanceReview {  
        int id PK "Primary Key"  
        int employeeId FK "Foreign Key"  
        date reviewDate  
        string feedback  
    }  
    Award {  
        int id PK "Primary Key"  
        int employeeId FK "Foreign Key"  
        string awardType  
        date awardDate  
    }  
    Project {  
        int id PK "Primary Key"  
        string name  
    }  
    EmployeeProject {  
        int id PK "Primary Key"  
        int employeeId FK "Foreign Key"  
        int projectId FK "Foreign Key"  
    }  

    Employee ||--o{ Payroll : has
    Employee ||--o{ Timesheet : fills
    Employee ||--o{ Leave : requests
    Employee ||--o{ Benefit : receives
    Employee ||--o{ Deduction : has
    Employee ||--o{ Overtime : claims
    Employee ||--o{ Tax : pays
    Employee ||--o{ EmployeeBenefits : has
    Employee ||--o{ EmployeeLeave : takes
    Employee ||--o{ Training : undergoes
    Employee ||--o{ PerformanceReview : receives
    Employee ||--o{ Award : wins
    EmployeeProject ||--o{ Employee : includes
    EmployeeProject ||--o{ Project : contains
    Payroll ||--o{ PaymentHistory : generates
    PayGrade ||--o{ Employee : includes
    Department ||--o{ Employee : manages
    ```
