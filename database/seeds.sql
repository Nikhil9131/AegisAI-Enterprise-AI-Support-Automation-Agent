-- AEGIS AI Enterprise Support & Business Automation Platform
-- Seed Data for MySQL / MariaDB / SQLite

-- 1. ROLES
INSERT INTO roles (id, name, description) VALUES
(1, 'ADMIN', 'System Administrator with full access to settings, users, AI analytics, and logs'),
(2, 'AGENT', 'Support Agent authorized to investigate, resolve tickets, and interact with agents'),
(3, 'EMPLOYEE', 'Standard enterprise employee with access to AI assistant, knowledge base, and ticket creation')
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- 2. USERS
-- Passwords:
-- Admin: Admin@123
-- Agent: Agent@123
-- Employee: User@123
INSERT INTO users (id, role_id, full_name, email, password_hash, department, phone, status) VALUES
(1, 1, 'Alexander Pierce', 'admin@aegis.enterprise', '$2y$10$gJNabSAViNXVecjSx6F7Q.o.8RUMCkf5Er1PyjJM4AMdqtYofuv8y', 'Executive / IT Ops', '+1-555-0100', 'ACTIVE'),
(2, 2, 'Sarah Jenkins', 'agent.sarah@aegis.enterprise', '$2y$10$W1jG5fLSrKPIETor/24NBOE6xVXLjUUnKbEGpWdojSmkvtD1iYXIa', 'IT Support Desk', '+1-555-0101', 'ACTIVE'),
(3, 2, 'Marcus Brody', 'agent.marcus@aegis.enterprise', '$2y$10$W1jG5fLSrKPIETor/24NBOE6xVXLjUUnKbEGpWdojSmkvtD1iYXIa', 'IT Infrastructure', '+1-555-0102', 'ACTIVE'),
(4, 3, 'Rahul Sharma', 'rahul.sharma@aegis.enterprise', '$2y$10$P/RHqBzgNSpUnBs6ek0uh.vzjEnsrzof3ZpQI2S/QmvWHtyr9Bta6', 'Engineering', '+1-555-0103', 'ACTIVE'),
(5, 3, 'Priya Patel', 'priya.patel@aegis.enterprise', '$2y$10$P/RHqBzgNSpUnBs6ek0uh.vzjEnsrzof3ZpQI2S/QmvWHtyr9Bta6', 'Product Management', '+1-555-0104', 'ACTIVE'),
(6, 3, 'Alex Chen', 'alex.chen@aegis.enterprise', '$2y$10$P/RHqBzgNSpUnBs6ek0uh.vzjEnsrzof3ZpQI2S/QmvWHtyr9Bta6', 'Cloud Architecture', '+1-555-0105', 'ACTIVE'),
(7, 3, 'Elena Rostova', 'elena.rostova@aegis.enterprise', '$2y$10$P/RHqBzgNSpUnBs6ek0uh.vzjEnsrzof3ZpQI2S/QmvWHtyr9Bta6', 'Human Resources', '+1-555-0106', 'ACTIVE'),
(8, 3, 'David Kim', 'david.kim@aegis.enterprise', '$2y$10$P/RHqBzgNSpUnBs6ek0uh.vzjEnsrzof3ZpQI2S/QmvWHtyr9Bta6', 'Finance', '+1-555-0107', 'ACTIVE'),
(9, 3, 'Fatima Al-Mansoor', 'fatima.m@aegis.enterprise', '$2y$10$P/RHqBzgNSpUnBs6ek0uh.vzjEnsrzof3ZpQI2S/QmvWHtyr9Bta6', 'Legal & Compliance', '+1-555-0108', 'ACTIVE'),
(10, 3, 'Chloe Zhao', 'chloe.zhao@aegis.enterprise', '$2y$10$P/RHqBzgNSpUnBs6ek0uh.vzjEnsrzof3ZpQI2S/QmvWHtyr9Bta6', 'Marketing & Communications', '+1-555-0109', 'ACTIVE')
ON DUPLICATE KEY UPDATE full_name=VALUES(full_name);

-- 3. EMPLOYEES (10 records)
INSERT INTO employees (id, user_id, employee_code, full_name, email, phone, department, job_title, manager_name, office_location, status) VALUES
(1, 1, 'EMP-1001', 'Alexander Pierce', 'admin@aegis.enterprise', '+1-555-0100', 'Executive / IT Ops', 'Chief Information Officer', 'Board of Directors', 'Headquarters - Executive Suite 400', 'ACTIVE'),
(2, 2, 'EMP-1002', 'Sarah Jenkins', 'agent.sarah@aegis.enterprise', '+1-555-0101', 'IT Support Desk', 'Senior Support Lead', 'Alexander Pierce', 'Headquarters - Floor 3 IT Wing', 'ACTIVE'),
(3, 3, 'EMP-1003', 'Marcus Brody', 'agent.marcus@aegis.enterprise', '+1-555-0102', 'IT Infrastructure', 'Network & Systems Engineer', 'Sarah Jenkins', 'Headquarters - Floor 3 IT Wing', 'ACTIVE'),
(4, 4, 'EMP-1004', 'Rahul Sharma', 'rahul.sharma@aegis.enterprise', '+1-555-0103', 'Engineering', 'Senior Staff Software Engineer', 'Alex Chen', 'Building B - Tech Labs 201', 'ACTIVE'),
(5, 5, 'EMP-1005', 'Priya Patel', 'priya.patel@aegis.enterprise', '+1-555-0104', 'Product Management', 'Principal Product Manager', 'Alexander Pierce', 'Building A - Product Hub 105', 'ACTIVE'),
(6, 6, 'EMP-1006', 'Alex Chen', 'alex.chen@aegis.enterprise', '+1-555-0105', 'Cloud Architecture', 'Director of Cloud & DevOps', 'Alexander Pierce', 'Building B - Tech Labs 204', 'ACTIVE'),
(7, 7, 'EMP-1007', 'Elena Rostova', 'elena.rostova@aegis.enterprise', '+1-555-0106', 'Human Resources', 'HR Operations Lead', 'Alexander Pierce', 'Building A - People Team 302', 'ACTIVE'),
(8, 8, 'EMP-1008', 'David Kim', 'david.kim@aegis.enterprise', '+1-555-0107', 'Finance', 'Financial Controller', 'Alexander Pierce', 'Building A - Finance Dept 310', 'ACTIVE'),
(9, 9, 'EMP-1009', 'Fatima Al-Mansoor', 'fatima.m@aegis.enterprise', '+1-555-0108', 'Legal & Compliance', 'Senior Corporate Counsel', 'Alexander Pierce', 'Building A - Legal Suite 405', 'ACTIVE'),
(10, 10, 'EMP-1010', 'Chloe Zhao', 'chloe.zhao@aegis.enterprise', '+1-555-0109', 'Marketing & Communications', 'Growth Marketing Director', 'Priya Patel', 'Building A - Marketing 215', 'ACTIVE')
ON DUPLICATE KEY UPDATE full_name=VALUES(full_name);

-- 4. DEVICES (10 records)
INSERT INTO devices (id, employee_id, device_name, device_type, os_version, serial_number, ip_address, mac_address, vpn_access_enabled, compliance_status, last_seen) VALUES
(1, 1, 'Pierce-MacBook-Pro16', 'LAPTOP', 'macOS Sonoma 14.5', 'C02G8790MD6R', '10.240.10.15', '3C:22:FB:44:81:AA', 1, 'COMPLIANT', NOW()),
(2, 2, 'Sarah-ThinkPad-T14', 'LAPTOP', 'Windows 11 Pro 23H2', 'PF39K7B2', '10.240.10.22', '00:1A:2B:3C:4D:5E', 1, 'COMPLIANT', NOW()),
(3, 3, 'Marcus-Precision-Workstation', 'WORKSTATION', 'Ubuntu Linux 24.04 LTS', 'SN-DELL-88412', '10.240.10.88', '08:00:27:81:92:44', 1, 'COMPLIANT', NOW()),
(4, 4, 'Rahul-MacBook-Pro14', 'LAPTOP', 'macOS Sonoma 14.4.1', 'C02H1899MD6S', '10.240.14.92', 'F0:18:98:23:45:67', 1, 'COMPLIANT', NOW()),
(5, 5, 'Priya-Dell-XPS15', 'LAPTOP', 'Windows 11 Pro 23H2', 'XPS-9530-5819', '10.240.12.33', 'A4:BB:6D:90:12:34', 1, 'COMPLIANT', NOW()),
(6, 6, 'Alex-ThinkPad-X1-Extreme', 'LAPTOP', 'Fedora 40 Workstation', 'TPX1-8899-AXC', '10.240.14.101', '78:AF:08:B3:55:19', 1, 'COMPLIANT', NOW()),
(7, 7, 'Elena-Surface-Laptop5', 'LAPTOP', 'Windows 11 Pro 22H2', 'MSFT-SL5-9921', '10.240.15.40', 'B8:27:EB:19:88:FF', 1, 'COMPLIANT', NOW()),
(8, 8, 'David-HP-EliteBook840', 'LAPTOP', 'Windows 11 Pro 23H2', 'HP-EB840-7711', '10.240.16.12', 'DC:A6:32:01:44:E2', 1, 'COMPLIANT', NOW()),
(9, 9, 'Fatima-MacBook-Air15', 'LAPTOP', 'macOS Sonoma 14.5', 'C02J4411MD7T', '10.240.17.55', 'E4:5F:01:67:89:BC', 1, 'COMPLIANT', NOW()),
(10, 10, 'Chloe-MacBook-Pro14', 'LAPTOP', 'macOS Ventura 13.6', 'C02F2299MD5P', '10.240.18.77', '9C:B6:D0:11:22:33', 1, 'PENDING_PATCH', NOW())
ON DUPLICATE KEY UPDATE device_name=VALUES(device_name);

-- 5. KNOWLEDGE CATEGORIES
INSERT INTO knowledge_categories (id, name, slug, description, icon) VALUES
(1, 'IT Security & Compliance', 'it-security', 'Corporate security controls, password policies, MFA requirements, and incident reporting protocols.', 'shield-check'),
(2, 'Network & VPN Infrastructure', 'network-vpn', 'Global WireGuard/OpenVPN setup, gateway endpoints, DNS configuration, and connectivity troubleshooting.', 'wifi'),
(3, 'Hardware & Device Management', 'hardware-devices', 'Standard laptop allocation, 3-year refresh cycles, peripheral ordering, repair and return procedures.', 'laptop'),
(4, 'HR Policies & Employee Benefits', 'hr-policies', 'Leave entitlements, paid time off, remote work allowances, healthcare and reimbursement schedules.', 'file-text'),
(5, 'Engineering & Developer Tools', 'developer-tools', 'Git access, internal registry, cloud sandbox credentials, CI/CD pipeline access, and production deployment gates.', 'code-slash')
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- 6. DOCUMENTS (Knowledge Base)
INSERT INTO documents (id, title, file_name, file_path, file_type, file_size, category_id, chunk_count, uploaded_by, status) VALUES
(1, 'Enterprise IT Security Policy 2026', 'IT_Security_Policy.txt', 'uploads/documents/IT_Security_Policy.txt', 'TXT', 14250, 1, 8, 1, 'INDEXED'),
(2, 'Global VPN Configuration & Troubleshooting Guide', 'VPN_Troubleshooting_Guide.txt', 'uploads/documents/VPN_Troubleshooting_Guide.txt', 'TXT', 11840, 2, 7, 2, 'INDEXED'),
(3, 'Hardware Refresh & Laptop Replacement Policy', 'Laptop_Replacement_Policy.txt', 'uploads/documents/Laptop_Replacement_Policy.txt', 'TXT', 9650, 3, 5, 1, 'INDEXED'),
(4, 'Enterprise Password & Authentication Policy', 'Password_Policy.txt', 'uploads/documents/Password_Policy.txt', 'TXT', 8420, 1, 4, 1, 'INDEXED'),
(5, 'Employee Annual Leave & Attendance Regulations', 'Leave_and_Attendance_Policy.txt', 'uploads/documents/Leave_and_Attendance_Policy.txt', 'TXT', 12300, 4, 6, 7, 'INDEXED'),
(6, 'New Hire IT & Cloud Onboarding Guide', 'Employee_Onboarding_Guide.txt', 'uploads/documents/Employee_Onboarding_Guide.txt', 'TXT', 15100, 4, 9, 2, 'INDEXED'),
(7, 'Enterprise Network & Zero Trust Troubleshooting', 'Network_Troubleshooting_Guide.txt', 'uploads/documents/Network_Troubleshooting_Guide.txt', 'TXT', 13780, 2, 8, 3, 'INDEXED'),
(8, 'Software Procurement & License Approval Standard', 'Software_Procurement_Policy.txt', 'uploads/documents/Software_Procurement_Policy.txt', 'TXT', 8900, 5, 5, 1, 'INDEXED'),
(9, 'Incident Response & Data Breach Playbook', 'Incident_Response_Playbook.txt', 'uploads/documents/Incident_Response_Playbook.txt', 'TXT', 16400, 1, 10, 1, 'INDEXED'),
(10, 'Cloud Infrastructure Access & IAM Role Policy', 'Cloud_Access_Policy.txt', 'uploads/documents/Cloud_Access_Policy.txt', 'TXT', 10500, 5, 6, 6, 'INDEXED')
ON DUPLICATE KEY UPDATE title=VALUES(title);

-- 7. TICKETS (20 realistic enterprise support tickets)
INSERT INTO tickets (id, ticket_number, user_id, assigned_agent_id, title, description, category, priority, status, ai_summary, ai_suggested_resolution, ai_confidence, requires_approval, approval_action, approval_status, created_at, resolved_at) VALUES
(1, 'TICK-8001', 4, 2, 'VPN Gateway handshake timeout on MacBook Pro', 'Unable to connect to the US-East WireGuard VPN gateway since this morning. The client displays Error 802 handshake timed out.', 'Network & VPN', 'HIGH', 'IN_PROGRESS', 'Employee Rahul Sharma experiencing repeated WireGuard VPN handshake timeout on macOS.', 'Renew certificate bundle from https://iam.aegis.enterprise/vpn and verify port 51820 egress.', 94.50, 0, NULL, NULL, DATE_SUB(NOW(), INTERVAL 4 HOUR), NULL),
(2, 'TICK-8002', 5, 2, 'Requesting laptop battery diagnostic and replacement', 'My Dell XPS 15 battery drains within 45 minutes on battery power. System health reports Fair battery status.', 'Hardware & Devices', 'MEDIUM', 'OPEN', 'Employee Priya Patel reports severe battery degradation on Dell XPS 15 (45 min runtime).', 'Run Dell Command Power Manager diagnostic; initiate battery replacement dispatch via IT procurement.', 88.00, 1, 'APPROVE_BATTERY_DISPATCH', 'PENDING', DATE_SUB(NOW(), INTERVAL 1 DAY), NULL),
(3, 'TICK-8003', 6, 3, 'Production EKS cluster IAM authentication webhook failure', 'Developers in EMEA region unable to assume role via aws-auth configmap. Receiving Unauthorized 401 on kubectl.', 'Developer Tools', 'CRITICAL', 'IN_PROGRESS', 'Production Kubernetes cluster authentication failure for EMEA IAM roles.', 'Rotate expired IAM OIDC provider thumbprint and patch AWS CLI configmap.', 96.00, 0, NULL, NULL, DATE_SUB(NOW(), INTERVAL 2 HOUR), NULL),
(4, 'TICK-8004', 7, 2, 'Annual PTO balance reconciliation for Q3', 'My dashboard shows 12 PTO days remaining, but Workday reflects 16 accrued days. Need IT/HR sync check.', 'HR Policies', 'LOW', 'RESOLVED', 'Discrepancy between internal HR dashboard and Workday PTO balance.', 'Triggered manual sync webhook with Workday HRIS; balance updated to 16 days.', 98.20, 0, NULL, NULL, DATE_SUB(NOW(), INTERVAL 5 DAY), DATE_SUB(NOW(), INTERVAL 4 DAY)),
(5, 'TICK-8005', 8, 3, 'Corporate Credit Card reconciliation portal access', 'Need access to SAP Concur Expense portal for Q3 audit preparation. Getting 403 Forbidden.', 'IT Security', 'HIGH', 'WAITING_FOR_USER', 'David Kim requesting privileged SAP Concur role permissions.', 'Verified Manager Alexander Pierce approval; user requested to clear SSO cookies and retry MFA.', 91.00, 0, NULL, NULL, DATE_SUB(NOW(), INTERVAL 3 DAY), NULL),
(6, 'TICK-8006', 4, 2, 'Requesting second 27-inch 4K monitor for development workstation', 'Desk setup currently has single monitor. Requesting approval for Dell UltraSharp U2723QE per equipment tier 2.', 'Hardware & Devices', 'LOW', 'OPEN', 'Per-policy hardware request for dual-monitor workstation upgrade.', 'Per Hardware Policy Section 3.2, Staff Engineers qualify for dual 4K monitors without escalation.', 93.00, 1, 'PURCHASE_HARDWARE', 'PENDING', DATE_SUB(NOW(), INTERVAL 2 DAY), NULL),
(7, 'TICK-8007', 9, 3, 'DocuSign Enterprise account provisioning for legal counsel', 'Need signature authority configuration on DocuSign enterprise tier for NDA and vendor contracts.', 'IT Security', 'MEDIUM', 'RESOLVED', 'Provisioning enterprise e-signature license for Corporate Legal counsel.', 'Assigned DocuSign Corporate Signer license and bound to Okta SSO.', 99.00, 0, NULL, NULL, DATE_SUB(NOW(), INTERVAL 6 DAY), DATE_SUB(NOW(), INTERVAL 5 DAY)),
(8, 'TICK-8008', 10, 2, 'Marketing Figma Enterprise seat upgrade', 'Current viewer license prevents editing design sprint assets. Please upgrade to Editor seat.', 'Developer Tools', 'MEDIUM', 'CLOSED', 'Chloe Zhao requested Figma Editor seat upgrade.', 'Approved by Design Director and provisioned via Figma SCIM.', 95.00, 0, NULL, NULL, DATE_SUB(NOW(), INTERVAL 10 DAY), DATE_SUB(NOW(), INTERVAL 9 DAY)),
(9, 'TICK-8009', 4, 3, 'Docker daemon permission denied on development environment', 'Unable to pull corporate base images from private AWS ECR repository: AccessDeniedException.', 'Developer Tools', 'HIGH', 'RESOLVED', 'Docker credential helper authentication failure for private ECR.', 'Updated aws-ecr-login credential store configuration in ~/.docker/config.json.', 97.50, 0, NULL, NULL, DATE_SUB(NOW(), INTERVAL 8 DAY), DATE_SUB(NOW(), INTERVAL 7 DAY)),
(10, 'TICK-8010', 5, 2, 'Zoom Rooms conference AV audio echo in Room 4B', 'When starting hybrid meetings in Room 4B, remote participants report high pitch feedback.', 'Hardware & Devices', 'MEDIUM', 'OPEN', 'Conference room AV hardware echo and mic cancellation failure.', 'Recalibrate Neat Bar audio beamforming and reset DSP processor.', 86.00, 0, NULL, NULL, DATE_SUB(NOW(), INTERVAL 1 DAY), NULL),
(11, 'TICK-8011', 6, 3, 'Datadog APM tracing agent high CPU usage on staging bastion', 'Staging bastion host reporting 95% CPU pinned on dd-agent process.', 'IT Infrastructure', 'MEDIUM', 'IN_PROGRESS', 'Datadog agent CPU spike caused by runaway log forwarder buffer.', 'Restart datadog-agent service with max_memory_limit set to 512MB.', 92.00, 0, NULL, NULL, DATE_SUB(NOW(), INTERVAL 18 HOUR), NULL),
(12, 'TICK-8012', 7, 2, 'Workplace ergonomic desk assessment request', 'Requesting an ergonomic keyboard and wrist rest following medical recommendation.', 'HR Policies', 'LOW', 'OPEN', 'Ergonomic equipment procurement under workplace wellness policy.', 'Approved per Employee Wellness Guideline Item 4; order placed with vendor.', 89.00, 1, 'APPROVE_ERGONOMIC_KIT', 'PENDING', DATE_SUB(NOW(), INTERVAL 3 DAY), NULL),
(13, 'TICK-8013', 8, 3, 'Finance Snowflake Data Warehouse query timeout', 'End of month financial batch job times out after 3600 seconds on WAREHOUSE_FIN_XL.', 'IT Infrastructure', 'HIGH', 'ESCALATED', 'Snowflake query execution timeout during monthly close.', 'Escalated to Database Architecture team to review query execution plan and clustering keys.', 85.00, 0, NULL, NULL, DATE_SUB(NOW(), INTERVAL 12 HOUR), NULL),
(14, 'TICK-8014', 9, 2, 'External compliance auditor guest Wi-Fi credentials', 'Visiting ISO 27001 auditors require 5-day isolated guest Wi-Fi access with 802.1X isolation.', 'Network & VPN', 'LOW', 'RESOLVED', 'Temporary isolated guest network provisioning for external auditors.', 'Generated 5-day expiring guest vouchers with VLAN 80 quarantine.', 99.50, 0, NULL, NULL, DATE_SUB(NOW(), INTERVAL 4 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY)),
(15, 'TICK-8015', 10, 2, 'Mailgun marketing email deliverability bounce alert', 'Automated newsletter campaign experienced 4.2% bounce rate on Microsoft 365 recipients.', 'IT Security', 'HIGH', 'RESOLVED', 'Email deliverability alert due to missing DKIM selector in DNS.', 'Updated Cloudflare DNS with verified Mailgun 2048-bit DKIM key.', 96.80, 0, NULL, NULL, DATE_SUB(NOW(), INTERVAL 7 DAY), DATE_SUB(NOW(), INTERVAL 6 DAY)),
(16, 'TICK-8016', 4, 3, 'Requesting access to production AWS CloudWatch logs for debugging', 'Need 48-hour temporary read-only access to /ecs/aegis-payment-service log group to diagnose bug.', 'IT Security', 'HIGH', 'OPEN', 'Rahul Sharma requesting temporary elevated CloudWatch read permission.', 'Requires Staff Security Manager approval per Zero-Trust Policy 8.1.', 93.40, 1, 'GRANT_TEMPORARY_IAM_ROLE', 'PENDING', DATE_SUB(NOW(), INTERVAL 6 HOUR), NULL),
(17, 'TICK-8017', 5, 2, 'Miro collaboration board Enterprise migration', 'Need to transfer legacy personal Miro boards to Aegis Enterprise organization space.', 'Workplace Tools', 'LOW', 'RESOLVED', 'Enterprise team board domain migration and permission consolidation.', 'Transferred ownership of 8 project boards to Aegis Enterprise Team.', 94.00, 0, NULL, NULL, DATE_SUB(NOW(), INTERVAL 12 DAY), DATE_SUB(NOW(), INTERVAL 11 DAY)),
(18, 'TICK-8018', 6, 3, 'Terraform state lock stuck on DynamoDB table', 'Pipeline blocked: Error acquiring state lock: ConditionalCheckFailedException in us-east-1.', 'Developer Tools', 'CRITICAL', 'RESOLVED', 'Terraform DynamoDB distributed lock stuck from interrupted CI job.', 'Verified no running pipeline; force-unlocked lock ID e091a4-9213 via terraform force-unlock.', 98.00, 0, NULL, NULL, DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY)),
(19, 'TICK-8019', 7, 2, 'Updating emergency contact details in HR system', 'Moved apartments last week. Need help updating address and emergency contact on HR portal.', 'HR Policies', 'LOW', 'CLOSED', 'Employee requesting self-service address and contact detail update procedure.', 'Guided employee to Employee Portal -> Profile -> Personal Information -> Save.', 99.80, 0, NULL, NULL, DATE_SUB(NOW(), INTERVAL 14 DAY), DATE_SUB(NOW(), INTERVAL 14 DAY)),
(20, 'TICK-8020', 8, 3, 'Suspicious phishing email reporting: FedEx Invoice #99812', 'Received an unexpected email with a macro-enabled Excel attachment claiming to be an overdue invoice.', 'IT Security', 'CRITICAL', 'RESOLVED', 'Phishing email report with malicious VBA macro payload.', 'Quarantined message across enterprise tenant; added sender domain to Proofpoint blocklist.', 99.90, 0, NULL, NULL, DATE_SUB(NOW(), INTERVAL 15 DAY), DATE_SUB(NOW(), INTERVAL 15 DAY))
ON DUPLICATE KEY UPDATE title=VALUES(title);

-- 8. TICKET COMMENTS
INSERT INTO ticket_comments (id, ticket_id, user_id, is_internal, comment, is_ai_generated, created_at) VALUES
(1, 1, 2, 0, 'Hi Rahul, looking into the WireGuard gateway logs now. Can you confirm if you are on the US-East or US-West profile?', 0, DATE_SUB(NOW(), INTERVAL 3 HOUR)),
(2, 1, 4, 0, 'Hi Sarah, I am using the US-East configuration (gateway-east.vpn.aegis.enterprise).', 0, DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(3, 1, NULL, 1, '[AI Agent Diagnosis] Verified client device Pierce-MacBook-Pro14 compliance: OK. Detected server-side WireGuard gateway re-keying event at 08:30 UTC. Recommended action: client profile re-sync.', 1, DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(4, 2, 5, 0, 'Attaching screenshot of Dell Command Power Manager showing 62% maximum health capacity.', 0, DATE_SUB(NOW(), INTERVAL 20 HOUR)),
(5, 3, 3, 0, 'Identified root cause: OIDC issuer thumbprint was overwritten by recent Terraform apply. Rolling back now.', 0, DATE_SUB(NOW(), INTERVAL 1 HOUR)),
(6, 16, NULL, 1, '[AI Security Guard] Sensitive Permission Request: Elevated access to Production CloudWatch Logs requires Human-In-The-Loop approval from Security Admin.', 1, DATE_SUB(NOW(), INTERVAL 5 HOUR))
ON DUPLICATE KEY UPDATE comment=VALUES(comment);

-- 9. AUDIT LOGS (Human-In-The-Loop & security events)
INSERT INTO audit_logs (id, user_id, action, entity_type, entity_id, details, ip_address, status, approved_by, approved_at, created_at) VALUES
(1, 1, 'CLOSE_TICKET', 'TICKET', 'TICK-8008', 'Administrator closed ticket after confirming Figma license provision.', '10.240.10.15', 'EXECUTED', 1, NOW(), DATE_SUB(NOW(), INTERVAL 9 DAY)),
(2, 4, 'REQUEST_ELEVATED_IAM', 'TICKET', 'TICK-8016', 'Rahul Sharma requested temporary 48h CloudWatch read access on /ecs/aegis-payment-service.', '10.240.14.92', 'PENDING', NULL, NULL, DATE_SUB(NOW(), INTERVAL 6 HOUR)),
(3, 5, 'PURCHASE_HARDWARE', 'TICKET', 'TICK-8002', 'Priya Patel battery replacement request awaiting Hardware Manager sign-off.', '10.240.12.33', 'PENDING', NULL, NULL, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(4, 1, 'USER_PERMISSION_UPDATE', 'USER', '2', 'Promoted Sarah Jenkins to Senior Support Lead role permissions.', '10.240.10.15', 'APPROVED', 1, DATE_SUB(NOW(), INTERVAL 30 DAY), DATE_SUB(NOW(), INTERVAL 30 DAY))
ON DUPLICATE KEY UPDATE action=VALUES(action);

-- 10. NOTIFICATIONS
INSERT INTO notifications (id, user_id, title, message, type, is_read, link, created_at) VALUES
(1, 4, 'Ticket Updated', 'Agent Sarah Jenkins posted a comment on your ticket TICK-8001.', 'INFO', 0, 'tickets/view/1', DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(2, 1, 'Approval Required', 'High priority ticket TICK-8016 requires human approval for elevated CloudWatch IAM role access.', 'WARNING', 0, 'admin/tickets', DATE_SUB(NOW(), INTERVAL 5 HOUR)),
(3, 5, 'Battery Request Under Review', 'Your hardware ticket TICK-8002 has been triaged by AI and is queued for manager approval.', 'INFO', 1, 'tickets/view/2', DATE_SUB(NOW(), INTERVAL 18 HOUR))
ON DUPLICATE KEY UPDATE title=VALUES(title);

-- 11. AI AGENT EXECUTION LOGS (Observability samples)
INSERT INTO ai_agent_logs (id, user_id, conversation_id, query, agent_selected, tools_used, retrieved_documents, retrieval_scores, response, latency_ms, token_usage, model, success, feedback, created_at) VALUES
(1, 4, 1, 'My laptop cannot connect to the company VPN. What should I do?', 'Supervisor -> Support Agent', '["qdrant_retriever", "mysql_device_lookup"]', '["VPN_Troubleshooting_Guide.txt", "IT_Security_Policy.txt"]', '[0.94, 0.82]', 'Based on your device profile (MacBook Pro, macOS 14.4.1), please verify that WireGuard profile is set to gateway-east.vpn.aegis.enterprise on UDP port 51820. If handshake fails after 30 seconds, re-generate client certificates at https://iam.aegis.enterprise/vpn.', 1240, 480, 'gemini-1.5-pro', 1, 'HELPFUL', DATE_SUB(NOW(), INTERVAL 3 HOUR)),
(2, 1, 2, 'How many open tickets are there and what is the highest priority issue?', 'Supervisor -> SQL Agent', '["mysql_readonly_sql"]', '[]', '[]', 'There are currently 6 open tickets and 3 in-progress tickets. The highest priority issue is TICK-8003: Production EKS cluster IAM authentication webhook failure (CRITICAL) assigned to Marcus Brody.', 680, 210, 'gemini-1.5-pro', 1, 'HELPFUL', DATE_SUB(NOW(), INTERVAL 2 HOUR))
ON DUPLICATE KEY UPDATE query=VALUES(query);
