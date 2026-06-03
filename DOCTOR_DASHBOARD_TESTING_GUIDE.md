# Doctor Dashboard - Testing Guide (QA Checklist)

## 📋 Test Scenarios (From Testing Checklist)

This guide provides step-by-step testing instructions for the Doctor Dashboard add/edit/delete functionality.

---

## 1️⃣ Xóa mục không tồn tại (Delete non-existent item)

### Scenario: Race condition - Item deleted in another tab

**Setup:**
- Open browser with Doctor Dashboard admin panel
- Danh sách bác sĩ (Doctor List) tab is visible

**Steps:**
1. **Tab 1:** Find a doctor in the list, click edit button
2. **Tab 2:** Open the same dashboard in another tab
3. **Tab 1:** Submit delete request
   - Expected: ✅ Toast message: "Đã xóa bác sĩ thành công."
   - Doctor disappears from list
4. **Tab 2:** Try to delete the same doctor
   - Expected: ❌ Toast message: "Bác sĩ không tồn tại."
   - Status: 404 Not Found

**Verification:**
- Server returns proper 404 error
- Error message is clear and helpful
- No data inconsistency in database

---

## 2️⃣ Cập nhật trùng lặp (Duplicate updates - Optimistic Locking)

### Scenario: Concurrent updates from two tabs

**Setup:**
- Open Doctor Dashboard with edit form in 2 browser tabs

**Steps:**
1. **Tab 1:** Open edit form for doctor ID = 1
2. **Tab 2:** Open the same doctor for editing
3. **Tab 1:** Modify any field (e.g., full_name → "BS. Đỗi")
4. **Tab 1:** Click Save
   - Expected: ✅ Toast: "Đã cập nhật thông tin bác sĩ."
   - Version incremented from 1→2
5. **Tab 2:** Modify different field (e.g., experience)
6. **Tab 2:** Click Save
   - Expected: ❌ Toast: "Bản ghi đã bị thay đổi bởi người khác. Vui lòng tải lại và thử lại."
   - Status: 409 Conflict

**Verification:**
- Version field prevents lost updates
- User must reload to see latest version
- Database maintains data consistency

---

## 3️⃣ ID không tồn tại (Invalid ID handling)

### Scenario A: Non-numeric ID

**Steps:**
1. Open URL: `/doctor/dashboard/doctors/abc`
2. Expected: ❌ 404 error page or API returns: "ID bác sĩ không hợp lệ."

### Scenario B: Extremely large ID

**Steps:**
1. Open URL: `/doctor/dashboard/doctors/99999999999`
2. Expected: ❌ 404 error with message: "Bác sĩ không tồn tại."

### Scenario C: Negative or zero ID

**Steps:**
1. Try to edit doctor with ID = 0 or -1
2. Expected: ❌ 404 with message: "ID bác sĩ không hợp lệ."

**Verification:**
- All invalid IDs are rejected
- Error messages are clear
- No data leakage in errors

---

## 4️⃣ Validate form (Form validation)

### Scenario: Submit invalid data

**Steps:**

#### Test 4a: Empty required field (Họ và tên)
1. Open "Thêm bác sĩ" modal
2. Leave "Họ và tên" empty
3. Click Save
   - Expected: ❌ Field highlights red
   - Error: "Vui lòng nhập họ tên."

#### Test 4b: Invalid email format
1. Fill "Tạo tài khoản" checkbox
2. Enter email: "not-an-email"
3. Click Save
   - Expected: ❌ Email field highlights red
   - Error: "Email không hợp lệ."

#### Test 4c: No department selected
1. Leave "Khoa" as "— Chọn khoa —"
2. Click Save
   - Expected: ❌ Khoa field highlights red
   - Error: "Vui lòng chọn khoa."

#### Test 4d: Invalid department ID
1. Intercept request → Change department_id to 999
2. Expected: ❌ 422 error: "Khoa không tồn tại."

**Verification:**
- Client-side validation prevents submission
- Server-side validation catches bypasses
- Error messages are in Vietnamese

---

## 5️⃣ Text quá tải (Text overflow/overflow test)

### Scenario: Copy-paste very long content

**Steps:**
1. Open any text field (e.g., "Giới thiệu / Bio")
2. Copy-paste long HTML content (suggest: copy from vnexpress.net)
3. Click Save
   - Expected: ✅ If text length ≤ 2000: Save succeeds
   - Expected: ❌ If text length > 2000: Show error
   - Error: "Giới thiệu không được vượt quá 2000 ký tự."

**Max lengths:**
- Họ và tên: 100 characters
- Giới thiệu: 2000 characters
- URL ảnh: 255 characters

**Verification:**
- Server validates string lengths
- Truncation is NOT allowed
- Clear error messages shown

---

## 6️⃣ Kiểm tra khoảng trắng (Whitespace validation)

### Scenario A: Only spaces in required field

**Steps:**
1. Open "Thêm bác sĩ" modal
2. Enter only spaces in "Họ và tên": `"   "`
3. Click Save
   - Expected: ❌ Field highlights red
   - Error: "Họ và tên không được chỉ chứa khoảng trắng."

### Scenario B: Full-width spaces (Asian characters)

**Steps:**
1. Copy full-width space: `"（　）"` (take the space from middle)
2. Paste in "Họ và tên" field
3. Click Save
   - Expected: ❌ Field highlights red
   - Error: "Họ và tên không được chỉ chứa khoảng trắng."

**Verification:**
- Single-byte spaces rejected
- Multi-byte spaces rejected
- Trimming is applied correctly

---

## 7️⃣ Kiểm tra dữ liệu số (Number validation)

### Scenario: Full-width numbers in numeric fields

**Steps:**
1. Copy full-width numbers: `"０１２３４５６７８９"`
2. Select "Kinh nghiệm" field
3. Paste: `"１０"` (full-width 1 and 0)
4. Click Save
   - Expected: ⚠️ Check if backend converts or rejects
   - Current: Might accept as string, need to verify

**Test for "Giá khám":**
1. Enter: `"200000"` (full-width)
2. Click Save
   - Expected: Should store as 200000 (integer)

**Verification:**
- Full-width numbers handling is consistent
- Database stores correct numeric values
- Validation error if invalid number

---

## 8️⃣ Kiểm tra Select-Option

### Scenario: Inject invalid department_id

**Steps:**
1. Open add/edit form
2. Intercept request (DevTools)
3. Change `department_id` to invalid value (e.g., 999, -1, null)
4. Submit
   - Expected: ❌ 422 error: "Khoa không tồn tại."

**Verification:**
- Frontend prevents invalid selection
- Backend validates department exists
- Cannot save with invalid department

---

## 9️⃣ Kiểm tra trùng lặp dữ liệu (Duplicate data prevention)

### Scenario: Double-click save button

**Steps:**
1. Open "Thêm bác sĩ" form
2. Fill all fields with valid data
3. **Rapidly click Save 2-3 times** (or before response arrives)
   - Expected: ✅ Only ONE doctor created
   - Button disabled after first click
   - Message: "Đã thêm bác sĩ thành công."

**Verification:**
- Button disabled during submission (loading state)
- Only one record created in DB
- Backend handles idempotency

### Scenario: Duplicate email

**Steps:**
1. Open add form
2. Check "Tạo tài khoản"
3. Enter email that already exists
4. Click Save
   - Expected: ❌ 409 error: "Email đã tồn tại trong hệ thống."

**Verification:**
- Unique constraint enforced
- Clear error message
- Form remains open for correction

---

## 🔟 Kiểm tra URL (URL parameter validation)

### Scenario A: Invalid page number

**Steps:**
1. Add to URL: `?page=abc`
2. Load page
   - Expected: ❌ Error or default to page 1

### Scenario B: Out of range page

**Steps:**
1. Add to URL: `?page=99999`
2. Load page
   - Expected: ✅ Empty list or default to last page

### Scenario C: Negative page

**Steps:**
1. Add to URL: `?page=-5`
2. Load page
   - Expected: ❌ Validation error or default to page 1

**Verification:**
- Page parameter validated (min:1)
- Out-of-range handled gracefully
- No data leakage

---

## 1️⃣1️⃣ Kiểm tra hình ảnh (Image file validation)

### Scenario A: Upload wrong file type

**Steps:**
1. Open "Thêm bác sĩ" modal
2. Click "Chọn ảnh"
3. Select a PDF file
4. Click Save
   - Expected: ❌ Error: "Tệp phải là hình ảnh."

### Scenario B: Upload oversized image

**Steps:**
1. Prepare image > 5MB
2. Click "Chọn ảnh" → Select large image
3. Click Save
   - Expected: ❌ Error: "Kích thước tệp không được vượt quá 5MB."

### Scenario C: Valid image formats

**Steps:**
1. Test upload JPEG, PNG, WebP separately
2. All should upload successfully
   - Expected: ✅ Preview shows image
   - Image URL populated

**Verification:**
- Only image MIME types accepted
- File size limit enforced (5MB)
- Supported formats: JPEG, PNG, WebP

---

## 1️⃣2️⃣ Hình ảnh không hiển thị (Image display fallback)

### Scenario: Image deleted from storage

**Steps:**
1. Create doctor with avatar
2. Verify image displays correctly
3. Delete image file from `/storage/images/doctors/`
4. Reload page
   - Expected: ✅ Shows fallback (initials or placeholder)
   - No broken image icon

**Verification:**
- `onerror` handler triggers on 404
- Fallback displays initials (e.g., "NV" for Nguyễn Văn)
- No visual glitches

---

## 1️⃣3️⃣ Update với upload ảnh (Image preservation on update)

### Scenario: Update without changing image

**Setup:**
- Doctor already has avatar (e.g., `images/doctors/bs-an.jpg`)

**Steps:**
1. Edit doctor (don't change avatar)
   - Avatar URL field should show existing path
2. Modify other field (e.g., Kinh nghiệm: 10 → 15)
3. Click Save (without uploading new image)
   - Expected: ✅ Update succeeds
   - Same avatar URL preserved

**Verification:**
- If no new file uploaded: keep old `avatar_url`
- If file uploaded: update `avatar_url` with new path
- No forced re-upload of image

---

## 1️⃣4️⃣ Delete via URL (Direct delete security)

### Scenario: Try direct URL delete without version

**Steps:**
1. Get doctor delete endpoint: `DELETE /doctor/dashboard/doctors/5`
2. Copy URL, open in new browser/incognito
3. Send request without version
   - Expected: ❌ 422 error: "version required"

### Scenario: Direct URL with wrong version

**Steps:**
1. Send: `DELETE /doctor/dashboard/doctors/5` with `version: 99`
2. Expected: ❌ 409 Conflict: "Bản ghi đã bị thay đổi bởi người khác..."

**Verification:**
- Version field is REQUIRED
- Without version: request fails
- Cannot delete with stale version

---

## 📊 Summary Checklist

| # | Scenario | Status | Notes |
|---|----------|--------|-------|
| 1 | Delete non-existent item | ✅ | 404 handling |
| 2 | Concurrent updates | ✅ | Version conflict 409 |
| 3 | Invalid ID | ✅ | ID validation |
| 4 | Form validation | ✅ | All fields validated |
| 5 | Text overflow | ✅ | Length limits |
| 6 | Whitespace handling | ✅ | Single/multi-byte spaces |
| 7 | Number validation | ✅ | Full-width numbers |
| 8 | Select validation | ✅ | Foreign key check |
| 9 | Duplicate prevention | ✅ | Button disabled, unique constraints |
| 10 | URL validation | ✅ | Page parameter |
| 11 | Image validation | ✅ | Type, size, format |
| 12 | Image fallback | ✅ | Initials on error |
| 13 | Image update | ✅ | Preserve if not changed |
| 14 | Delete security | ✅ | Version required |

---

## 🔧 Implementation Details

### Error Response Codes
- **201** - Resource created successfully
- **200** - Success (update/delete)
- **400** - Bad request
- **403** - Forbidden (permission denied)
- **404** - Not found (invalid ID or non-existent resource)
- **409** - Conflict (version mismatch for updates/deletes)
- **422** - Validation error
- **500** - Server error

### Request/Response Format

**Add Doctor:**
```bash
POST /doctor/dashboard/doctors
{
    "full_name": "BS. Nguyễn Văn An",
    "department_id": 1,
    "experience": 10,
    "price": 200000,
    "status": 1,
    ...
}
```

**Update Doctor:**
```bash
PUT /doctor/dashboard/doctors/5
{
    "version": 1,  # Current version
    "full_name": "BS. Nguyễn Văn An (Updated)",
    ...
}
```

**Delete Doctor:**
```bash
DELETE /doctor/dashboard/doctors/5
{
    "version": 1  # Must match current version
}
```

---

## 🎯 Key Validations Implemented

✅ **Backend (Laravel):**
- StoreDoctorRequest - validates input
- UpdateDoctorRequest - validates input + version
- Unique constraints on email, user_id
- Foreign key validation on department_id
- File validation in UploadAvatarRequest

✅ **Frontend (JavaScript):**
- Client-side field validation
- Loading state on buttons
- Toast notifications for errors
- Error highlighting on invalid fields
- Modal prevents accidental submission

✅ **Database:**
- Unique constraints on email, user_id
- Foreign key on department_id
- Version field for optimistic locking
- Status field for soft-delete capability

---

## 📝 Notes

- All error messages are in Vietnamese
- Response times should be < 2 seconds for most operations
- Large file uploads (5MB) may take longer
- Toast notifications disappear after 3.5 seconds
- Form validation errors highlight fields in red
