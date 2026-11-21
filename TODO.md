- [ ] Fix parse error / remove conflict markers and duplicated garbled code in app/Http/Controllers/Doctor/DashboardController.php
- [ ] Restore/clean the Doctors CRUD methods block (doctorsList, getDoctor, storeDoctor if present, updateDoctor, destroyDoctor, uploadAvatar)
- [ ] Add optimistic locking for updateDoctor using doctors.version (already present idea)
- [ ] Add optimistic locking for destroyDoctor using request.version and return 409 on mismatch
- [ ] Ensure delete is consistent: do not allow delete+update to override (409 should be returned)
- [ ] Run `php -l` for DashboardController and execute a quick route smoke test if available

