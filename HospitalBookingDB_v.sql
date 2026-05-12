-- ============================================================
-- Database: HospitalBookingDB
-- ============================================================
CREATE DATABASE IF NOT EXISTS HospitalBookingDB;
USE HospitalBookingDB;

-- ============================================================
-- 1. ROLES
-- ============================================================
CREATE TABLE Roles (
    role_id   INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL UNIQUE
);

-- ============================================================
-- 2. USERS
-- ============================================================
CREATE TABLE Users (
    user_id       INT AUTO_INCREMENT PRIMARY KEY,
    full_name     VARCHAR(100) NOT NULL,
    email         VARCHAR(100) NOT NULL UNIQUE,
    password      VARCHAR(255) NOT NULL,
    phone         VARCHAR(15),
    address       VARCHAR(255),
    date_of_birth DATE,
    gender        VARCHAR(10),
    role_id       INT NOT NULL,
    avatar_url    VARCHAR(500),
    status        BOOLEAN DEFAULT TRUE,
    created_at    DATETIME DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT FK_Users_Roles FOREIGN KEY (role_id) REFERENCES Roles(role_id),
    CONSTRAINT CK_Users_Gender CHECK (gender IN ('Nam', 'Nữ', 'Khác'))
);

-- ============================================================
-- 3. DEPARTMENTS
-- ============================================================
CREATE TABLE Departments (
    department_id   INT AUTO_INCREMENT PRIMARY KEY,
    department_name VARCHAR(100) NOT NULL UNIQUE,
    description     VARCHAR(500),
    status          BOOLEAN DEFAULT TRUE
);

-- ============================================================
-- 4. ROOMS
-- ============================================================
CREATE TABLE Rooms (
    room_id       INT AUTO_INCREMENT PRIMARY KEY,
    room_code     VARCHAR(20) NOT NULL UNIQUE,
    room_name     VARCHAR(100),
    department_id INT,
    room_type     VARCHAR(50) NOT NULL,
    status        VARCHAR(30) DEFAULT 'Trống',
    notes         VARCHAR(255),

    CONSTRAINT FK_Rooms_Departments FOREIGN KEY (department_id) REFERENCES Departments(department_id),
    CONSTRAINT CK_Rooms_Type CHECK (room_type IN ('Khám', 'Thủ thuật', 'Siêu âm', 'Xét nghiệm')),
    CONSTRAINT CK_Rooms_Status CHECK (status IN ('Đang sử dụng', 'Trống', 'Bảo trì', 'Vệ sinh'))
);

-- ============================================================
-- 5. DOCTORS
-- ============================================================
CREATE TABLE Doctors (
    doctor_id     INT AUTO_INCREMENT PRIMARY KEY,
    user_id       INT NOT NULL UNIQUE,
    full_name     VARCHAR(100) NOT NULL,
    department_id INT NOT NULL,
    experience    INT CHECK (experience >= 0),
    price         DECIMAL(10,2) CHECK (price >= 0),
    avatar_url    VARCHAR(500),
    bio           VARCHAR(1000),
    status        BOOLEAN DEFAULT TRUE,

    CONSTRAINT FK_Doctors_Users FOREIGN KEY (user_id) REFERENCES Users(user_id),
    CONSTRAINT FK_Doctors_Departments FOREIGN KEY (department_id) REFERENCES Departments(department_id)
);

-- ============================================================
-- 6. DOCTOR SCHEDULES
-- ============================================================
CREATE TABLE DoctorSchedules (
    schedule_id   INT AUTO_INCREMENT PRIMARY KEY,
    doctor_id     INT NOT NULL,
    room_id       INT,
    work_date     DATE NOT NULL,
    start_time    TIME NOT NULL,
    end_time      TIME NOT NULL,
    slot_duration INT NOT NULL DEFAULT 30,
    max_slot      INT CHECK (max_slot > 0),
    status        VARCHAR(30) DEFAULT 'Hoạt động',
    note          VARCHAR(255),

    CONSTRAINT FK_DoctorSchedules_Doctors FOREIGN KEY (doctor_id) REFERENCES Doctors(doctor_id),
    CONSTRAINT FK_DoctorSchedules_Rooms FOREIGN KEY (room_id) REFERENCES Rooms(room_id),
    CONSTRAINT CK_DoctorSchedules_Time CHECK (start_time < end_time),
    CONSTRAINT UQ_DoctorSchedules UNIQUE (doctor_id, work_date, start_time)
);

-- ============================================================
-- 7. DOCTOR DAYS OFF
-- ============================================================
CREATE TABLE DoctorDaysOff (
    day_off_id  INT AUTO_INCREMENT PRIMARY KEY,
    doctor_id   INT NOT NULL,
    off_date    DATE NOT NULL,
    reason      VARCHAR(255),
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT FK_DoctorDaysOff_Doctors FOREIGN KEY (doctor_id) REFERENCES Doctors(doctor_id),
    CONSTRAINT UQ_DoctorDaysOff UNIQUE (doctor_id, off_date)
);

-- ============================================================
-- 8. SERVICES
-- ============================================================
CREATE TABLE Services (
    service_id       INT AUTO_INCREMENT PRIMARY KEY,
    service_code     VARCHAR(30) NOT NULL UNIQUE,
    service_name     VARCHAR(150) NOT NULL,
    department_id    INT,
    description      VARCHAR(500),
    duration_minutes INT DEFAULT 30,
    status           BOOLEAN DEFAULT TRUE,

    CONSTRAINT FK_Services_Departments FOREIGN KEY (department_id) REFERENCES Departments(department_id)
);

-- ============================================================
-- 9. SERVICE PRICES
-- ============================================================
CREATE TABLE ServicePrices (
    price_id       INT AUTO_INCREMENT PRIMARY KEY,
    service_id     INT NOT NULL,
    price_type     VARCHAR(30) NOT NULL,
    price          DECIMAL(10,2) NOT NULL CHECK (price >= 0),
    effective_date DATE NOT NULL DEFAULT (CURRENT_DATE),
    end_date       DATE,
    created_by     INT,
    created_at     DATETIME DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT FK_ServicePrices_Services FOREIGN KEY (service_id) REFERENCES Services(service_id),
    CONSTRAINT FK_ServicePrices_Users FOREIGN KEY (created_by) REFERENCES Users(user_id),
    CONSTRAINT CK_ServicePrices_Type CHECK (price_type IN ('Thường', 'BHYT', 'VIP', 'Theo yêu cầu'))
);

-- ============================================================
-- 10. APPOINTMENTS
-- ============================================================
CREATE TABLE Appointments (
    appointment_id   INT AUTO_INCREMENT PRIMARY KEY,
    user_id          INT NOT NULL,
    schedule_id      INT NOT NULL,
    service_id       INT,
    appointment_time DATETIME,
    queue_number     INT,
    status           VARCHAR(50) DEFAULT 'Chờ xác nhận',
    note             VARCHAR(255),
    cancel_reason    VARCHAR(255),
    slot_hold_expire DATETIME,
    rescheduled_from INT,
    created_at       DATETIME DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT FK_Appointments_Users FOREIGN KEY (user_id) REFERENCES Users(user_id),
    CONSTRAINT FK_Appointments_DoctorSchedules FOREIGN KEY (schedule_id) REFERENCES DoctorSchedules(schedule_id),
    CONSTRAINT FK_Appointments_Services FOREIGN KEY (service_id) REFERENCES Services(service_id),
    CONSTRAINT FK_Appointments_Rescheduled FOREIGN KEY (rescheduled_from) REFERENCES Appointments(appointment_id),
    CONSTRAINT UQ_Appointments_UserSchedule UNIQUE (user_id, schedule_id),
    CONSTRAINT CK_Appointments_Status CHECK (status IN (
        'Chờ xác nhận', 'Đã xác nhận', 'Đang khám',
        'Hoàn thành', 'Đã hủy', 'Dời lịch', 'Giữ slot'
    ))
);

-- Trigger: kiểm tra max_slot trước khi insert
DELIMITER $$
CREATE TRIGGER TR_Appointments_CheckMaxSlot
BEFORE INSERT ON Appointments
FOR EACH ROW
BEGIN
    DECLARE slot_count INT;
    
    SELECT COUNT(*) INTO slot_count
    FROM Appointments a
    WHERE a.schedule_id = NEW.schedule_id
      AND a.status NOT IN ('Đã hủy', 'Dời lịch', 'Giữ slot');
    
    IF slot_count >= (SELECT max_slot FROM DoctorSchedules WHERE schedule_id = NEW.schedule_id) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Lịch khám đã đủ số lượng bệnh nhân tối đa.';
    END IF;
END$$
DELIMITER ;

-- ============================================================
-- 11. CHECK-IN & QUEUE
-- ============================================================
CREATE TABLE CheckIns (
    checkin_id       INT AUTO_INCREMENT PRIMARY KEY,
    appointment_id   INT NOT NULL UNIQUE,
    checkin_time     DATETIME DEFAULT CURRENT_TIMESTAMP,
    queue_number     INT,
    est_wait_minutes INT,
    called_at        DATETIME,
    status           VARCHAR(30) DEFAULT 'Đang chờ',

    CONSTRAINT FK_CheckIns_Appointments FOREIGN KEY (appointment_id)
        REFERENCES Appointments(appointment_id) ON DELETE CASCADE
);

-- ============================================================
-- 12. INSURANCE
-- ============================================================
CREATE TABLE InsuranceCards (
    insurance_id  INT AUTO_INCREMENT PRIMARY KEY,
    user_id       INT NOT NULL,
    card_number   VARCHAR(50) NOT NULL,
    provider      VARCHAR(100),
    issued_date   DATE,
    expiry_date   DATE,
    discount_pct  DECIMAL(5,2) DEFAULT 0 CHECK (discount_pct BETWEEN 0 AND 100),
    status        VARCHAR(20) DEFAULT 'Còn hạn',
    created_at    DATETIME DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT FK_Insurance_Users FOREIGN KEY (user_id) REFERENCES Users(user_id)
);

-- ============================================================
-- 13. MEMBERSHIP
-- ============================================================
CREATE TABLE MembershipCards (
    card_id      INT AUTO_INCREMENT PRIMARY KEY,
    user_id      INT NOT NULL UNIQUE,
    card_number  VARCHAR(50) NOT NULL UNIQUE,
    tier         VARCHAR(30) DEFAULT 'Thường',
    points       INT DEFAULT 0,
    discount_pct DECIMAL(5,2) DEFAULT 0,
    issue_date   DATE NOT NULL DEFAULT (CURRENT_DATE),
    expiry_date  DATE,
    status       BOOLEAN DEFAULT TRUE,

    CONSTRAINT FK_MembershipCards_Users FOREIGN KEY (user_id) REFERENCES Users(user_id),
    CONSTRAINT CK_MembershipCards_Tier CHECK (tier IN ('Thường', 'Bạc', 'Vàng', 'Kim cương'))
);

-- ============================================================
-- 14. PAYMENTS
-- ============================================================
CREATE TABLE Payments (
    payment_id      INT AUTO_INCREMENT PRIMARY KEY,
    appointment_id  INT NOT NULL UNIQUE,
    insurance_id    INT,
    membership_id   INT,
    subtotal        DECIMAL(10,2) CHECK (subtotal >= 0),
    discount_amount DECIMAL(10,2) DEFAULT 0,
    total_amount    DECIMAL(10,2) CHECK (total_amount >= 0),
    method          VARCHAR(50),
    status          VARCHAR(30) DEFAULT 'Chưa thanh toán',
    transaction_ref VARCHAR(100),
    payment_date    DATETIME DEFAULT CURRENT_TIMESTAMP,
    notes           VARCHAR(255),

    CONSTRAINT FK_Payments_Appointments FOREIGN KEY (appointment_id) REFERENCES Appointments(appointment_id) ON DELETE CASCADE,
    CONSTRAINT FK_Payments_Insurance FOREIGN KEY (insurance_id) REFERENCES InsuranceCards(insurance_id),
    CONSTRAINT FK_Payments_Membership FOREIGN KEY (membership_id) REFERENCES MembershipCards(card_id),
    CONSTRAINT CK_Payments_Status CHECK (status IN ('Chưa thanh toán', 'Đã thanh toán', 'Hoàn tiền', 'Còn nợ'))
);

-- ============================================================
-- 15. PAYMENT ITEMS
-- ============================================================
CREATE TABLE PaymentItems (
    item_id     INT AUTO_INCREMENT PRIMARY KEY,
    payment_id  INT NOT NULL,
    item_type   VARCHAR(30) NOT NULL,
    item_name   VARCHAR(150),
    quantity    INT DEFAULT 1,
    unit_price  DECIMAL(10,2),
    total_price DECIMAL(10,2),

    CONSTRAINT FK_PaymentItems_Payments FOREIGN KEY (payment_id) REFERENCES Payments(payment_id) ON DELETE CASCADE,
    CONSTRAINT CK_PaymentItems_Type CHECK (item_type IN ('Khám', 'Dịch vụ', 'Thuốc'))
);

-- ============================================================
-- 16. MEDICAL RECORDS
-- ============================================================
CREATE TABLE MedicalRecords (
    record_id      INT AUTO_INCREMENT PRIMARY KEY,
    appointment_id INT NOT NULL UNIQUE,
    user_id        INT NOT NULL,
    doctor_id      INT,
    diagnosis      VARCHAR(500),
    prescription   VARCHAR(1000),
    follow_up_date DATE,
    file_path      VARCHAR(500),
    notes          VARCHAR(500),
    created_at     DATETIME DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT FK_MedicalRecords_Appointments FOREIGN KEY (appointment_id) REFERENCES Appointments(appointment_id),
    CONSTRAINT FK_MedicalRecords_Users FOREIGN KEY (user_id) REFERENCES Users(user_id),
    CONSTRAINT FK_MedicalRecords_Doctors FOREIGN KEY (doctor_id) REFERENCES Doctors(doctor_id)
);

-- ============================================================
-- 17. MEDICAL DOCUMENTS
-- ============================================================
CREATE TABLE MedicalDocuments (
    doc_id      INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,
    record_id   INT,
    doc_type    VARCHAR(50),
    doc_name    VARCHAR(200),
    file_path   VARCHAR(500),
    uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT FK_MedicalDocuments_Users FOREIGN KEY (user_id) REFERENCES Users(user_id),
    CONSTRAINT FK_MedicalDocuments_Records FOREIGN KEY (record_id) REFERENCES MedicalRecords(record_id)
);

-- ============================================================
-- 18. ALLERGIES
-- ============================================================
CREATE TABLE PatientAllergies (
    allergy_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    allergen   VARCHAR(150) NOT NULL,
    reaction   VARCHAR(255),
    severity   VARCHAR(30),
    noted_date DATE DEFAULT (CURRENT_DATE),
    notes      VARCHAR(255),

    CONSTRAINT FK_PatientAllergies_Users FOREIGN KEY (user_id) REFERENCES Users(user_id)
);

-- ============================================================
-- 19. MEDICAL HISTORY
-- ============================================================
CREATE TABLE PatientMedicalHistory (
    history_id   INT AUTO_INCREMENT PRIMARY KEY,
    user_id      INT NOT NULL,
    `condition`  VARCHAR(150) NOT NULL,
    diagnosed_at DATE,
    treated_at   VARCHAR(200),
    is_chronic   BOOLEAN DEFAULT FALSE,
    notes        VARCHAR(255),

    CONSTRAINT FK_PatientHistory_Users FOREIGN KEY (user_id) REFERENCES Users(user_id)
);

-- ============================================================
-- 20. TREATMENT REMINDERS
-- ============================================================
CREATE TABLE TreatmentReminders (
    reminder_id    INT AUTO_INCREMENT PRIMARY KEY,
    user_id        INT NOT NULL,
    record_id      INT,
    reminder_type  VARCHAR(50),
    remind_at      DATETIME NOT NULL,
    message        VARCHAR(500),
    is_sent        BOOLEAN DEFAULT FALSE,
    created_at     DATETIME DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT FK_TreatmentReminders_Users FOREIGN KEY (user_id) REFERENCES Users(user_id),
    CONSTRAINT FK_TreatmentReminders_Records FOREIGN KEY (record_id) REFERENCES MedicalRecords(record_id)
);

-- ============================================================
-- 21. VACCINES
-- ============================================================
CREATE TABLE Vaccines (
    vaccine_id      INT AUTO_INCREMENT PRIMARY KEY,
    vaccine_name    VARCHAR(150) NOT NULL,
    description     VARCHAR(500),
    manufacturer    VARCHAR(100),
    doses_required  INT DEFAULT 1,
    status          BOOLEAN DEFAULT TRUE
);

CREATE TABLE VaccinationRecords (
    vaccination_id   INT AUTO_INCREMENT PRIMARY KEY,
    user_id          INT NOT NULL,
    vaccine_id       INT NOT NULL,
    doctor_id        INT,
    dose_number      INT DEFAULT 1,
    administered_at  DATETIME,
    batch_number     VARCHAR(50),
    next_dose_date   DATE,
    status           VARCHAR(20) DEFAULT 'Chưa tiêm',
    notes            VARCHAR(255),

    CONSTRAINT FK_VaccRec_Users FOREIGN KEY (user_id) REFERENCES Users(user_id),
    CONSTRAINT FK_VaccRec_Vaccines FOREIGN KEY (vaccine_id) REFERENCES Vaccines(vaccine_id),
    CONSTRAINT FK_VaccRec_Doctors FOREIGN KEY (doctor_id) REFERENCES Doctors(doctor_id)
);

-- ============================================================
-- 22. PHARMACY
-- ============================================================
CREATE TABLE Medicines (
    medicine_id    INT AUTO_INCREMENT PRIMARY KEY,
    medicine_code  VARCHAR(30) UNIQUE,
    medicine_name  VARCHAR(150) NOT NULL,
    unit           VARCHAR(30),
    unit_price     DECIMAL(10,2),
    stock_quantity INT DEFAULT 0,
    min_stock      INT DEFAULT 10,
    expiry_date    DATE,
    status         BOOLEAN DEFAULT TRUE
);

CREATE TABLE MedicineTransactions (
    transaction_id INT AUTO_INCREMENT PRIMARY KEY,
    medicine_id    INT NOT NULL,
    trans_type     VARCHAR(10) NOT NULL,
    quantity       INT NOT NULL,
    unit_price     DECIMAL(10,2),
    reference_id   INT,
    note           VARCHAR(255),
    created_by     INT,
    created_at     DATETIME DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT FK_MediTrans_Medicines FOREIGN KEY (medicine_id) REFERENCES Medicines(medicine_id),
    CONSTRAINT FK_MediTrans_Users FOREIGN KEY (created_by) REFERENCES Users(user_id),
    CONSTRAINT CK_MediTrans_Type CHECK (trans_type IN ('Nhập', 'Xuất'))
);

-- ============================================================
-- 23. REVIEWS
-- ============================================================
CREATE TABLE Reviews (
    review_id      INT AUTO_INCREMENT PRIMARY KEY,
    appointment_id INT NOT NULL UNIQUE,
    user_id        INT NOT NULL,
    doctor_id      INT NOT NULL,
    rating         INT CHECK (rating BETWEEN 1 AND 5),
    comment        VARCHAR(500),
    doctor_reply   VARCHAR(500),
    created_at     DATETIME DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT FK_Reviews_Appointments FOREIGN KEY (appointment_id) REFERENCES Appointments(appointment_id),
    CONSTRAINT FK_Reviews_Users FOREIGN KEY (user_id) REFERENCES Users(user_id),
    CONSTRAINT FK_Reviews_Doctors FOREIGN KEY (doctor_id) REFERENCES Doctors(doctor_id)
);

-- View: Rating bác sĩ
CREATE OR REPLACE VIEW V_DoctorRatings AS
    SELECT
        d.doctor_id, d.full_name, d.department_id, d.experience, d.price,
        d.avatar_url, d.bio, d.status,
        COALESCE(AVG(r.rating), 0) AS avg_rating,
        COUNT(r.review_id) AS total_reviews
    FROM Doctors d
    LEFT JOIN Reviews r ON r.doctor_id = d.doctor_id
    GROUP BY d.doctor_id, d.full_name, d.department_id,
             d.experience, d.price, d.avatar_url, d.bio, d.status;

-- ============================================================
-- 24. CHAT
-- ============================================================
CREATE TABLE ChatRooms (
    room_id    INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    doctor_id  INT,
    status     VARCHAR(20) DEFAULT 'Mở',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    closed_at  DATETIME,

    CONSTRAINT FK_ChatRooms_Users FOREIGN KEY (user_id) REFERENCES Users(user_id),
    CONSTRAINT FK_ChatRooms_Doctors FOREIGN KEY (doctor_id) REFERENCES Doctors(doctor_id)
);

CREATE TABLE ChatMessages (
    message_id   INT AUTO_INCREMENT PRIMARY KEY,
    room_id      INT NOT NULL,
    sender_id    INT NOT NULL,
    message_text VARCHAR(2000),
    is_read      BOOLEAN DEFAULT FALSE,
    sent_at      DATETIME DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT FK_ChatMessages_Rooms FOREIGN KEY (room_id) REFERENCES ChatRooms(room_id),
    CONSTRAINT FK_ChatMessages_Sender FOREIGN KEY (sender_id) REFERENCES Users(user_id)
);

-- ============================================================
-- 25. HOSPITAL NEWS
-- ============================================================
CREATE TABLE HospitalNews (
    news_id      INT AUTO_INCREMENT PRIMARY KEY,
    title        VARCHAR(300) NOT NULL,
    content      TEXT,
    category     VARCHAR(50),
    thumbnail    VARCHAR(500),
    author_id    INT,
    is_published BOOLEAN DEFAULT FALSE,
    published_at DATETIME,
    created_at   DATETIME DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT FK_HospitalNews_Author FOREIGN KEY (author_id) REFERENCES Users(user_id)
);

-- ============================================================
-- 26. NOTIFICATIONS
-- ============================================================
CREATE TABLE Notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT NOT NULL,
    notif_type      VARCHAR(50),
    title           VARCHAR(200),
    content         VARCHAR(1000),
    ref_id          INT,
    ref_type        VARCHAR(30),
    is_read         BOOLEAN DEFAULT FALSE,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT FK_Notifications_Users FOREIGN KEY (user_id) REFERENCES Users(user_id)
);

-- ============================================================
-- 27. ACTIVITY LOGS
-- ============================================================
CREATE TABLE ActivityLogs (
    log_id     INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    action     VARCHAR(255),
    ip_address VARCHAR(45),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT FK_ActivityLogs_Users FOREIGN KEY (user_id) REFERENCES Users(user_id)
);

-- ============================================================
-- SAMPLE DATA
-- ============================================================

-- Roles
INSERT INTO Roles (role_name) VALUES ('Admin'),('Bác sĩ'),('Bệnh nhân');

-- Users
INSERT INTO Users (full_name, email, password, phone, address, role_id) VALUES
('Nguyễn Văn A',  'a@gmail.com',     '123456', '0901111111', 'Hà Nội',   3),
('Trần Thị B',    'b@gmail.com',     '123456', '0902222222', 'HCM',      3),
('Lê Văn C',      'c@gmail.com',     '123456', '0903333333', 'Đà Nẵng',  2),
('Phạm Thị D',    'd@gmail.com',     '123456', '0904444444', 'Hà Nội',   2),
('Hoàng Văn E',   'e@gmail.com',     '123456', '0905555555', 'HCM',      2),
('Vũ Thị F',      'f@gmail.com',     '123456', '0906666666', 'Đà Nẵng',  2),
('Đặng Văn G',    'g@gmail.com',     '123456', '0907777777', 'Hà Nội',   2),
('Admin',         'admin@gmail.com', 'admin',  '0900000000', 'Hà Nội',   1);

-- Departments
INSERT INTO Departments (department_name, description) VALUES
('Nội tổng quát', 'Khám tổng quát'),
('Nhi khoa',      'Khám trẻ em'),
('Tim mạch',      'Chuyên tim mạch'),
('Da liễu',       'Bệnh da liễu'),
('Xương khớp',    'Cơ xương khớp');

-- Rooms
INSERT INTO Rooms (room_code, room_name, department_id, room_type, status) VALUES
('P101', 'Phòng khám 101', 1, 'Khám',       'Trống'),
('P102', 'Phòng khám 102', 2, 'Khám',       'Trống'),
('P201', 'Phòng siêu âm',  3, 'Siêu âm',    'Trống'),
('P301', 'Phòng thủ thuật',4, 'Thủ thuật',  'Trống'),
('P401', 'Phòng xét nghiệm',5,'Xét nghiệm', 'Trống');

-- Doctors
INSERT INTO Doctors (user_id, full_name, department_id, experience, price, bio) VALUES
(3, 'Bác sĩ Minh',  1, 10, 200000, 'Chuyên gia Nội tổng quát với 10 năm kinh nghiệm'),
(4, 'Bác sĩ Lan',   2,  8, 150000, 'Bác sĩ Nhi khoa tận tâm'),
(5, 'Bác sĩ Hùng',  3, 12, 300000, 'Chuyên gia Tim mạch hàng đầu'),
(6, 'Bác sĩ Trang', 4,  6, 180000, 'Bác sĩ Da liễu kinh nghiệm'),
(7, 'Bác sĩ Nam',   5,  9, 220000, 'Chuyên gia Cơ xương khớp');

-- DoctorSchedules
INSERT INTO DoctorSchedules (doctor_id, room_id, work_date, start_time, end_time, slot_duration, max_slot) VALUES
(1, 1, '2026-04-20', '08:00:00', '12:00:00', 30, 8),
(2, 2, '2026-04-20', '13:00:00', '17:00:00', 30, 8),
(3, 1, '2026-04-21', '08:00:00', '12:00:00', 30, 8),
(4, 4, '2026-04-21', '13:00:00', '17:00:00', 30, 8),
(5, 5, '2026-04-22', '08:00:00', '12:00:00', 30, 8);

-- Services
INSERT INTO Services (service_code, service_name, department_id, duration_minutes) VALUES
('DV001', 'Khám tổng quát',          1, 30),
('DV002', 'Siêu âm tim',             3, 45),
('DV003', 'Xét nghiệm máu tổng quát', 1, 20),
('DV004', 'Điện tim (ECG)',          3, 20),
('DV005', 'Khám da liễu',            4, 30);

-- ServicePrices
INSERT INTO ServicePrices (service_id, price_type, price) VALUES
(1, 'Thường',       200000),
(1, 'BHYT',         50000),
(1, 'VIP',          400000),
(2, 'Thường',       350000),
(3, 'Thường',       180000);

-- Appointments
INSERT INTO Appointments (user_id, schedule_id, service_id, queue_number, status) VALUES
(1, 1, 1, 1, 'Đã xác nhận'),
(2, 2, 1, 1, 'Đã xác nhận'),
(1, 3, 2, 1, 'Đã xác nhận'),
(2, 4, 5, 1, 'Chờ xác nhận'),
(1, 5, 1, 1, 'Chờ xác nhận');

-- CheckIns
INSERT INTO CheckIns (appointment_id, queue_number, est_wait_minutes, status) VALUES
(1, 1, 0,  'Đang khám'),
(2, 1, 15, 'Đang chờ'),
(3, 1, 0,  'Hoàn thành');

-- Payments
INSERT INTO Payments (appointment_id, subtotal, discount_amount, total_amount, method, status) VALUES
(1, 200000, 0, 200000, 'Tiền mặt',     'Đã thanh toán'),
(2, 150000, 0, 150000, 'Chuyển khoản', 'Đã thanh toán'),
(3, 300000, 0, 300000, 'Tiền mặt',     'Chưa thanh toán');

-- MedicalRecords
INSERT INTO MedicalRecords (appointment_id, user_id, doctor_id, diagnosis, prescription, follow_up_date) VALUES
(1, 1, 1, 'Viêm họng cấp',  'Amoxicillin 500mg x 7 ngày', '2026-05-01'),
(2, 2, 2, 'Sốt virus',      'Paracetamol 500mg, nghỉ ngơi', NULL),
(3, 1, 3, 'Nhịp tim nhanh', 'Theo dõi, tái khám sau 1 tuần', '2026-04-28');

-- Reviews
INSERT INTO Reviews (appointment_id, user_id, doctor_id, rating, comment) VALUES
(1, 1, 1, 5, 'Bác sĩ rất tận tâm'),
(2, 2, 2, 4, 'Khám kỹ, tư vấn tốt');

-- Vaccines
INSERT INTO Vaccines (vaccine_name, doses_required) VALUES
('Cúm mùa', 1),
('Viêm gan B', 3),
('COVID-19', 2);

-- Medicines
INSERT INTO Medicines (medicine_code, medicine_name, unit, unit_price, stock_quantity, min_stock) VALUES
('TH001', 'Paracetamol 500mg', 'Viên', 500,   1000, 100),
('TH002', 'Amoxicillin 500mg', 'Viên', 2000,  500,  50),
('TH003', 'Vitamin C 1000mg',  'Viên', 1500,  800,  80);

-- Hospital News
INSERT INTO HospitalNews (title, category, content, author_id, is_published, published_at) VALUES
('Thông báo lịch nghỉ lễ 30/4 - 1/5', 'Thông báo',
 'Bệnh viện sẽ hoạt động xuyên suốt trong dịp lễ 30/4 - 1/5 với đội ngũ trực đầy đủ.', 8, 1, NOW()),
('Chương trình khám sức khỏe miễn phí', 'Sự kiện',
 'Từ 15/5 đến 30/5, bệnh viện tổ chức chương trình khám miễn phí cho người cao tuổi.', 8, 1, NOW());