# ISO-Compliant Staff Welfare Loan Workflow Specification

## Document Control
- Document Title: Staff Welfare Loan – ISO-Compliant Workflow
- Document ID: ISO-SWL-001
- Revision: Rev 2
- ISO Standard: ISO 9001:2015
- Document Owner: Human Resources Department
- Approval Authority: Management Representative
- Effective Date: YYYY-MM-DD
- Review Date: YYYY-MM-DD

---

## 1. Purpose
This document defines the controlled electronic workflow for processing Staff Welfare Loan applications,
from initial request through approval, payment execution, and employee acknowledgement of debt.
The workflow ensures compliance with ISO 9001:2015 by enforcing document control, approval authority,
traceability, segregation of duties, data integrity, and records retention.

---

## 2. Scope
This workflow applies to:
- All employees requesting Staff Welfare Loans
- All approvers involved in the approval process
- Finance personnel responsible for loan disbursement
- Employees acknowledging loan liability

The workflow is implemented in a Laravel-based system using Livewire components,
Repository and Service layers. Controllers are not used.

---

## 3. Definitions
- Applicant: Employee submitting a Staff Welfare Loan request
- Approver: Authorized individual approving or rejecting a request
- Authorization Code: Secure, time-bound, single-use code required for approval actions
- Progressive Data Capture: Controlled addition of data by authorized roles at specific steps
- Audit Trail: Immutable record of all workflow actions
- Proof of Payment: Documented evidence of loan disbursement
- Acknowledgement of Debt: Formal acceptance by the employee of repayment obligation

---

## 4. Roles and Responsibilities

### Applicant
- Completes and submits the Staff Welfare Loan form
- Cannot modify submitted data
- Acknowledges debt after payment is made

### Supervisor
- Reviews operational justification
- Approves or rejects using authorization code
- Cannot modify form data
- comments

### Human Resources Manager
- Reviews applicant-submitted data
- Captures HR-controlled information
- Approves or rejects using authorization code

### Finance Manager
- Reviews financial feasibility
- Approves or rejects using authorization code

### CEO
- comments
- Provides final approval authority
- Authorizes loan disbursement

### Finance Officer (Payments)
- Executes loan payment
- Uploads proof of payment
- Cannot approve or reject loan requests

### Super Admin
- Maintains workflow configuration, permissions, and access control

---

## 5. Form Data Structure

The Staff Welfare Loan record is divided into **role-scoped sections**.

### 5.1 Applicant Section (Immutable After Submission)
- Employee Number
- Full Name - get from users table
- Department - get from department_users table
- Job Title
- Date Joined
- Loan Amount Requested
- Loan Purpose
- Repayment Period (Months)
- Applicant Digital Declaration
- Submission Date

### 5.2 Human Resources Section (Editable Only at HR Step)
- Employment Status
- Date of Engagement
- Basic Salary
- Monthly Deduction Amount
- Existing Loan Balance
- Monthly Repayment
- Last Payment Date
- HR Comments
- HR Digital Confirmation
- HR Review Date

### 5.3 Finance Payment Section (Editable Only After MD Approval)
- Amount Paid
- Payment Method
- Payment Reference
- Payment Date
- Proof of Payment (Uploaded File)
- Finance Officer Confirmation
- Payment Capture Date

### 5.4 Employee Acknowledgement Section (Final Step)
- Acknowledgement of Debt Statement
- Employee Digital Acceptance
- Acceptance Date

---

## 6. Workflow Overview
1. Applicant creates form and saves as draft
2. Applicant submits form
3. Supervisor approval
4. HR review and HR data capture
5. Finance Manager approval
6. CEO approval
7. Finance Officer executes payment and uploads proof
8. Applicant acknowledges debt
9. Workflow completion and archiving

---

## 7. Workflow Status Definitions
- Draft: Form saved but not submitted
- Submitted: Awaiting Supervisor approval
- Under Review: Pending intermediate approvals
- Approved: Fully approved by CEO
- Payment Processed: Payment executed by Finance
- Awaiting Employee Acknowledgement: Payment complete, awaiting acceptance
- Completed: Fully executed and acknowledged
- Rejected: Rejected at any approval stage
- Archived: Locked record for retention

Status transitions are enforced strictly by the Service layer.

---

## 8. Approval and Execution Controls

### 8.1 Approval and Execution Sequence
1. Supervisor – approval (authorization code required)
2. HR Manager – approval + HR data capture (authorization code required)
3. Finance Manager – approval (authorization code required)
4. CEO – final approval (authorization code required)
5. Finance Officer – payment execution (no approval rights)
6. Applicant – acknowledgement of debt

### 8.2 Authorization Code Rules
- Mandatory for all approval and rejection actions
- Single-use
- Time-limited
- Securely hashed
- Validation failure prevents action

Authorization codes are not required for payment upload or employee acknowledgement.

---

## 9. Segregation of Duties
- Applicants cannot approve their own requests
- Approvers cannot execute payments
- Finance Officers cannot approve requests
- HR cannot modify applicant-submitted data
- Employees must acknowledge debt before workflow completion
- Workflow steps cannot be skipped or reordered

---

## 10. Audit Trail and Traceability
The system must record:
- User ID
- User role
- Action performed
- Workflow step
- Timestamp
- Authorization code validation result
- Uploaded document metadata and hash
- Employee acknowledgement confirmation

Audit records are immutable and retained according to records management policy.

---

## 11. Records and Reporting

### 11.1 Records Maintained
- Loan application data
- Approval history
- HR enrichment data
- Payment proof documents
- Employee acknowledgement records

### 11.2 Reports
- Full loan lifecycle report
- Approval and authorization trail
- Payment and disbursement report
- Employee acknowledgement confirmation

Reports are available in PDF and CSV formats and are permission-controlled.

---

## 12. Data Security and Integrity
- Role-based and step-based access control
- Section-level field locking
- Encrypted sensitive data fields
- Secure document storage
- Database transactions for approvals and payments

---
# ISO Staff Welfare Loan – Permission Specification

## 1. Purpose
This document defines the permission set required to implement the
ISO-Compliant Staff Welfare Loan Workflow.

Roles are assumed to already exist.
This document specifies **permissions only**, which must be enforced
via policies, gates, or permission middleware.

---

## 2. Permission Design Principles
- Permissions are **action-based**, not role-based
- Roles are assigned permissions externally
- Permissions enforce:
  - Segregation of duties
  - Workflow step isolation
  - ISO 9001 traceability
- No user may perform actions outside their assigned permissions

---

## 3. Core Workflow Permissions

### 3.1 Application Permissions
| Permission Key | Description |
|---------------|------------|
| swl.create | Create Staff Welfare Loan draft |
| swl.edit.draft | Edit draft before submission |
| swl.submit | Submit loan application |
| swl.view.own | View own loan records |

---

### 3.2 Supervisor Approval Permissions
| Permission Key | Description |
|---------------|------------|
| swl.approve.supervisor | Approve loan at Supervisor step |
| swl.reject.supervisor | Reject loan at Supervisor step |
| swl.view.supervisor.queue | View loans pending supervisor approval |

---

### 3.3 Human Resources Permissions
| Permission Key | Description |
|---------------|------------|
| swl.approve.hr | Approve loan at HR step |
| swl.reject.hr | Reject loan at HR step |
| swl.edit.hr.section | Edit HR-controlled form section |
| swl.view.hr.queue | View loans pending HR review |

---

### 3.4 Finance Manager Permissions
| Permission Key | Description |
|---------------|------------|
| swl.approve.finance | Approve loan at Finance Manager step |
| swl.reject.finance | Reject loan at Finance Manager step |
| swl.view.finance.queue | View loans pending finance approval |

---

### 3.5 Managing Director Permissions
| Permission Key | Description |
|---------------|------------|
| swl.approve.md | Final approval authority |
| swl.reject.md | Final rejection authority |
| swl.view.md.queue | View loans pending MD approval |

---

## 4. Post-Approval Execution Permissions

### 4.1 Finance Officer (Payments)
| Permission Key | Description |
|---------------|------------|
| swl.payment.execute | Capture payment details |
| swl.payment.upload | Upload proof of payment |
| swl.view.payment.queue | View loans approved for payment |

> Note: Finance Officer must **NOT** have any approval permissions.

---

### 4.2 Employee Acknowledgement
| Permission Key | Description |
|---------------|------------|
| swl.acknowledge.debt | Acknowledge debt after payment |
| swl.view.acknowledgement | View acknowledgement status |

---

## 5. Reporting & Audit Permissions
| Permission Key | Description |
|---------------|------------|
| swl.report.view | View Staff Welfare Loan reports |
| swl.report.export | Export reports (PDF, CSV) |
| swl.audit.view | View audit trail (read-only) |

---

## 6. Administrative Permissions
| Permission Key | Description |
|---------------|------------|
| swl.workflow.configure | Configure workflow steps |
| swl.permission.manage | Assign permissions to roles |
| swl.view.all | View all loan records |

---

## 7. Permission Enforcement Rules
- Permissions are enforced via:
  - Policies or Gates
  - Service-layer validation
- UI visibility must reflect permission checks
- Backend must **always re-validate permissions**
- Approval actions must additionally require authorization code validation

---

## 8. ISO 9001:2015 Alignment
| ISO Clause | Enforcement |
|----------|------------|
| 5.3 | Clear responsibility via permissions |
| 7.5 | Controlled access to documented information |
| 8.1 | Operational control via permission gating |
| 9.1 | Audit and reporting access control |
| 10.2 | Controlled corrective actions |

---

## 9. Permission Naming Convention
- Prefix: `swl`
- Format: `swl.<action>.<scope>`
- Example: `swl.approve.hr`

permissions will be assigned later to the roles but they need to be applied to the views at implementation