<?php

class TransportService
{
    private mysqli $conn;
    private array $columnCache = [];

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    public function isReady(): bool
    {
        foreach (['buses', 'drivers', 'helpers', 'routes', 'stops', 'route_stops', 'bus_assignments'] as $table) {
            if (!$this->tableExists($table)) {
                return false;
            }
        }

        return true;
    }

    public function getModules(): array
    {
        return [
            'buses' => [
                'label' => 'Buses',
                'singular' => 'Bus',
                'icon' => 'fa-bus',
                'table' => 'buses',
                'pk' => 'bus_id',
                'order' => 'bus_number ASC',
                'fields' => [
                    'bus_number' => ['label' => 'Bus Number', 'type' => 'text', 'required' => true],
                    'registration_number' => ['label' => 'Registration Number', 'type' => 'text', 'required' => true],
                    'capacity' => ['label' => 'Capacity', 'type' => 'number', 'required' => true],
                    'driver_id' => ['label' => 'Driver', 'type' => 'select-source', 'source' => 'drivers'],
                    'helper_id' => ['label' => 'Helper', 'type' => 'select-source', 'source' => 'helpers'],
                    'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['active' => 'Active', 'inactive' => 'Inactive', 'maintenance' => 'Maintenance']],
                ],
            ],
            'drivers' => [
                'label' => 'Drivers',
                'singular' => 'Driver',
                'icon' => 'fa-id-card',
                'table' => 'drivers',
                'pk' => 'driver_id',
                'order' => 'name ASC',
                'fields' => [
                    'photo' => ['label' => 'Photo', 'type' => 'text'],
                    'name' => ['label' => 'Name', 'type' => 'text', 'required' => true],
                    'phone' => ['label' => 'Phone', 'type' => 'text', 'required' => true],
                    'license_number' => ['label' => 'License Number', 'type' => 'text', 'required' => true],
                    'license_expiry' => ['label' => 'License Expiry', 'type' => 'date'],
                    'address' => ['label' => 'Address', 'type' => 'textarea'],
                    'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['active' => 'Active', 'inactive' => 'Inactive']],
                ],
            ],
            'helpers' => [
                'label' => 'Helpers',
                'singular' => 'Helper',
                'icon' => 'fa-handshake-angle',
                'table' => 'helpers',
                'pk' => 'helper_id',
                'order' => 'name ASC',
                'fields' => [
                    'photo' => ['label' => 'Photo', 'type' => 'text'],
                    'name' => ['label' => 'Name', 'type' => 'text', 'required' => true],
                    'phone' => ['label' => 'Phone', 'type' => 'text', 'required' => true],
                    'address' => ['label' => 'Address', 'type' => 'textarea'],
                    'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['active' => 'Active', 'inactive' => 'Inactive']],
                ],
            ],
            'routes' => [
                'label' => 'Routes',
                'singular' => 'Route',
                'icon' => 'fa-route',
                'table' => 'routes',
                'pk' => 'route_id',
                'order' => 'route_name ASC',
                'fields' => [
                    'bus_id' => ['label' => 'Bus', 'type' => 'select-source', 'source' => 'buses'],
                    'route_name' => ['label' => 'Route Name', 'type' => 'text', 'required' => true],
                    'start_location' => ['label' => 'Start Location', 'type' => 'text'],
                    'end_location' => ['label' => 'End Location', 'type' => 'text'],
                    'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['active' => 'Active', 'inactive' => 'Inactive']],
                ],
            ],
            'stops' => [
                'label' => 'Stops',
                'singular' => 'Stop',
                'icon' => 'fa-location-dot',
                'table' => 'stops',
                'pk' => 'stop_id',
                'order' => 'stop_name ASC',
                'fields' => [
                    'stop_name' => ['label' => 'Stop Name', 'type' => 'text', 'required' => true],
                    'latitude' => ['label' => 'Latitude', 'type' => 'number', 'step' => '0.0000001'],
                    'longitude' => ['label' => 'Longitude', 'type' => 'number', 'step' => '0.0000001'],
                ],
            ],
            'route_stops' => [
                'label' => 'Route Stops',
                'singular' => 'Route Stop',
                'icon' => 'fa-timeline',
                'table' => 'route_stops',
                'pk' => 'route_stop_id',
                'order' => 'stop_order ASC',
                'fields' => [
                    'route_id' => ['label' => 'Route', 'type' => 'select-source', 'source' => 'routes', 'required' => true],
                    'stop_id' => ['label' => 'Stop', 'type' => 'select-source', 'source' => 'stops', 'required' => true],
                    'arrival_time' => ['label' => 'Arrival Time', 'type' => 'time'],
                    'departure_time' => ['label' => 'Departure Time', 'type' => 'time'],
                    'stop_order' => ['label' => 'Stop Order', 'type' => 'number', 'required' => true],
                ],
            ],
            'assignments' => [
                'label' => 'Assignments',
                'singular' => 'Assignment',
                'icon' => 'fa-users',
                'table' => 'bus_assignments',
                'pk' => 'assignment_id',
                'order' => 'assignment_id DESC',
                'fields' => [
                    'student_id' => ['label' => 'Student', 'type' => 'select-source', 'source' => 'students', 'required' => true],
                    'bus_id' => ['label' => 'Bus', 'type' => 'select-source', 'source' => 'buses', 'required' => true],
                    'route_id' => ['label' => 'Route', 'type' => 'select-source', 'source' => 'routes', 'required' => true],
                    'pickup_stop_id' => ['label' => 'Pickup Stop', 'type' => 'select-source', 'source' => 'stops'],
                    'drop_stop_id' => ['label' => 'Drop Stop', 'type' => 'select-source', 'source' => 'stops'],
                    'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['active' => 'Active', 'inactive' => 'Inactive']],
                ],
            ],
        ];
    }

    public function getModule(string $key): ?array
    {
        $modules = $this->getModules();
        return $modules[$key] ?? null;
    }

    public function getStats(): array
    {
        return [
            'buses' => $this->tableExists('buses') ? $this->scalar('SELECT COUNT(*) FROM buses') : 0,
            'active_routes' => $this->tableExists('routes') ? $this->scalar($this->hasColumn('routes', 'status') ? "SELECT COUNT(*) FROM routes WHERE status='active'" : 'SELECT COUNT(*) FROM routes') : 0,
            'assignments' => $this->tableExists('bus_assignments') ? $this->scalar('SELECT COUNT(*) FROM bus_assignments') : 0,
            'drivers' => $this->tableExists('drivers') ? $this->scalar('SELECT COUNT(*) FROM drivers') : 0,
            'helpers' => $this->tableExists('helpers') ? $this->scalar('SELECT COUNT(*) FROM helpers') : 0,
        ];
    }

    public function list(string $module_key): array
    {
        $module = $this->getModule($module_key);
        if (!$module || !$this->tableExists($module['table'])) {
            return [];
        }

        if ($module_key === 'assignments') {
            return $this->getAssignments();
        }

        if ($module_key === 'route_stops') {
            return $this->getRouteStops();
        }

        $order = $this->resolveOrder($module);
        $rows = [];
        $query = mysqli_query($this->conn, "SELECT * FROM {$module['table']} ORDER BY $order");
        while ($query && $row = mysqli_fetch_assoc($query)) {
            $rows[] = $this->normalizeRow($module_key, $row);
        }

        return $rows;
    }

    public function find(string $module_key, int $id): ?array
    {
        $module = $this->getModule($module_key);
        if (!$module || $id <= 0 || !$this->tableExists($module['table'])) {
            return null;
        }

        $pk = $module['pk'];
        $row = mysqli_fetch_assoc(mysqli_query(
            $this->conn,
            "SELECT * FROM {$module['table']} WHERE $pk = '$id' LIMIT 1"
        ));

        return $row ? $this->normalizeRow($module_key, $row) : null;
    }

    public function save(string $module_key, array $data, ?int $id = null): bool
    {
        $module = $this->getModule($module_key);
        if (!$module || !$this->tableExists($module['table'])) {
            return false;
        }

        $values = $this->prepareValues($module_key, $module, $data);
        if (empty($values)) {
            return false;
        }

        if ($id !== null && $id > 0) {
            $sets = [];
            foreach ($values as $column => $value) {
                $sets[] = "`$column` = " . $this->sqlValue($value);
            }

            return (bool) mysqli_query(
                $this->conn,
                "UPDATE {$module['table']} SET " . implode(', ', $sets) . " WHERE {$module['pk']} = '$id'"
            );
        }

        $columns = array_keys($values);
        $sqlColumns = '`' . implode('`,`', $columns) . '`';
        $sqlValues = implode(',', array_map(fn($value) => $this->sqlValue($value), array_values($values)));

        return (bool) mysqli_query(
            $this->conn,
            "INSERT INTO {$module['table']} ($sqlColumns) VALUES ($sqlValues)"
        );
    }

    public function delete(string $module_key, int $id): bool
    {
        $module = $this->getModule($module_key);
        if (!$module || $id <= 0 || !$this->tableExists($module['table'])) {
            return false;
        }

        return (bool) mysqli_query(
            $this->conn,
            "DELETE FROM {$module['table']} WHERE {$module['pk']} = '$id'"
        );
    }

    public function sourceOptions(string $source): array
    {
        switch ($source) {
            case 'buses':
                return $this->optionRows('buses', 'bus_id', "CONCAT(bus_number, ' - ', registration_number)");
            case 'drivers':
                return $this->optionRows('drivers', 'driver_id', 'name');
            case 'helpers':
                return $this->optionRows('helpers', 'helper_id', 'name');
            case 'routes':
                return $this->optionRows('routes', 'route_id', 'route_name');
            case 'stops':
                return $this->optionRows('stops', 'stop_id', 'stop_name');
            case 'students':
                $rows = [];
                $query = mysqli_query(
                    $this->conn,
                    "SELECT st.id, st.student_id, u.full_name, c.class_name, s.section_name
                     FROM students st
                     INNER JOIN users u ON u.id = st.user_id
                     LEFT JOIN classes c ON c.class_id = st.class_id
                     LEFT JOIN sections s ON s.section_id = st.section_id
                     ORDER BY u.full_name ASC"
                );
                while ($query && $row = mysqli_fetch_assoc($query)) {
                    $label = $row['full_name'] . ' (' . $row['student_id'] . ')';
                    if (!empty($row['class_name'])) {
                        $label .= ' - ' . $row['class_name'] . ' ' . ($row['section_name'] ?? '');
                    }
                    $rows[] = ['id' => $row['id'], 'label' => $label];
                }
                return $rows;
        }

        return [];
    }

    public function getStudentsPerRoute(): array
    {
        if (!$this->tableExists('bus_assignments')) {
            return ['labels' => [], 'values' => []];
        }

        $labels = [];
        $values = [];

        $query = mysqli_query(
            $this->conn,
            "SELECT
                r.route_name,
                COUNT(ba.assignment_id) AS total
            FROM routes r
            LEFT JOIN bus_assignments ba
                ON ba.route_id = r.route_id
            GROUP BY r.route_id, r.route_name
            ORDER BY r.route_name ASC"
        );

        while ($query && $row = mysqli_fetch_assoc($query)) {
            $labels[] = $row['route_name'];
            $values[] = (int)$row['total'];
        }

        return [
            'labels' => $labels,
            'values' => $values
        ];
    }

    public function getBusCapacityUsage(): array
    {
        if (!$this->tableExists('buses')) {
            return ['labels' => [], 'values' => []];
        }

        $labels = [];
        $values = [];
        $query = mysqli_query(
            $this->conn,
            "SELECT b.bus_number, b.capacity, COUNT(ba.assignment_id) AS assigned
             FROM buses b
             LEFT JOIN bus_assignments ba ON ba.bus_id = b.bus_id
             GROUP BY b.bus_id, b.bus_number, b.capacity
             ORDER BY b.bus_number ASC"
        );

        while ($query && $row = mysqli_fetch_assoc($query)) {
            $labels[] = $row['bus_number'];
            $capacity = max(1, (int) $row['capacity']);
            $values[] = round(((int) $row['assigned'] / $capacity) * 100, 2);
        }

        return ['labels' => $labels, 'values' => $values];
    }

    public function getDriverAssignmentByUser(int $userId): ?array
    {
        if (!$this->currentTransportReady()) {
            return null;
        }

        $userId = (int) $userId;
        $query = mysqli_query(
            $this->conn,
            "SELECT ts.staff_id, ts.full_name, ts.phone, ts.profile_photo,
                    b.bus_id, b.bus_name, b.bus_number, b.capacity, b.status AS bus_status,
                    r.route_id, r.route_name, r.route_color, r.start_time, r.end_time,
                    ll.latitude, ll.longitude, ll.status AS tracking_status, ll.updated_at
             FROM transport_staff ts
             LEFT JOIN transport_buses b ON b.driver_id = ts.staff_id
             LEFT JOIN transport_routes r ON r.bus_id = b.bus_id
             LEFT JOIN transport_live_location ll ON ll.bus_id = b.bus_id
             WHERE ts.user_id = '$userId' AND ts.staff_type = 'driver'
             LIMIT 1"
        );

        return $query && mysqli_num_rows($query) > 0 ? $this->withRouteMeta(mysqli_fetch_assoc($query)) : null;
    }

    public function getStudentAssignmentByUser(int $userId): ?array
    {
        if (!$this->currentTransportReady()) {
            return null;
        }

        $student = mysqli_fetch_assoc(mysqli_query(
            $this->conn,
            "SELECT id FROM students WHERE user_id = '" . (int) $userId . "' LIMIT 1"
        ));

        return $student ? $this->getStudentTransportAssignment((int) $student['id']) : null;
    }

    public function getParentAssignmentsByUser(int $userId): array
    {
        if (!$this->currentTransportReady()) {
            return [];
        }

        $studentCodes = [];
        $query = mysqli_query(
            $this->conn,
            "SELECT student_id FROM parents WHERE user_id = '" . (int) $userId . "'"
        );

        while ($query && $row = mysqli_fetch_assoc($query)) {
            if (!empty($row['student_id'])) {
                $studentCodes[] = "'" . mysqli_real_escape_string($this->conn, $row['student_id']) . "'";
            }
        }

        if (empty($studentCodes)) {
            return [];
        }

        $items = [];
        $students = mysqli_query(
            $this->conn,
            "SELECT id FROM students WHERE student_id IN (" . implode(',', array_unique($studentCodes)) . ")"
        );

        while ($students && $student = mysqli_fetch_assoc($students)) {
            $assignment = $this->getStudentTransportAssignment((int) $student['id']);
            if ($assignment) {
                $items[] = $assignment;
            }
        }

        return $items;
    }

    public function getStudentTransportAssignment(int $studentDbId): ?array
    {
        if (!$this->currentTransportReady() || $studentDbId <= 0) {
            return null;
        }

        $query = mysqli_query(
            $this->conn,
            "SELECT stu.id AS student_db_id, stu.student_id, u.full_name AS student_name,
                    c.class_name, sec.section_name,
                    b.bus_id, b.bus_name, b.bus_number, b.capacity,
                    d.full_name AS driver_name, d.phone AS driver_phone, d.profile_photo AS driver_photo,
                    h.full_name AS helper_name, h.phone AS helper_phone,
                    r.route_id, r.route_name, r.route_color, r.start_time, r.end_time,
                    st.stop_id AS assigned_stop_id, st.stop_name AS assigned_stop_name,
                    st.latitude AS assigned_stop_latitude, st.longitude AS assigned_stop_longitude,
                    ll.latitude, ll.longitude, ll.status AS tracking_status, ll.updated_at
             FROM transport_student_assignments tsa
             INNER JOIN students stu ON stu.id = tsa.student_id
             INNER JOIN users u ON u.id = stu.user_id
             LEFT JOIN classes c ON c.class_id = stu.class_id
             LEFT JOIN sections sec ON sec.section_id = stu.section_id
             INNER JOIN transport_buses b ON b.bus_id = tsa.bus_id
             LEFT JOIN transport_staff d ON d.staff_id = b.driver_id
             LEFT JOIN transport_staff h ON h.staff_id = b.helper_id
             LEFT JOIN transport_routes r ON r.bus_id = b.bus_id
             LEFT JOIN transport_stops st ON st.stop_id = tsa.stop_id
             LEFT JOIN transport_live_location ll ON ll.bus_id = b.bus_id
             WHERE tsa.student_id = '" . (int) $studentDbId . "'
             LIMIT 1"
        );

        return $query && mysqli_num_rows($query) > 0 ? $this->withRouteMeta(mysqli_fetch_assoc($query)) : null;
    }

    public function getLiveBus(int $busId): ?array
    {
        if (!$this->tableExists('transport_buses') || $busId <= 0) {
            return null;
        }

        $query = mysqli_query($this->conn, $this->liveBusSql("WHERE b.bus_id = '" . (int) $busId . "'", 'LIMIT 1'));

        return $query && mysqli_num_rows($query) > 0 ? $this->formatLiveBus(mysqli_fetch_assoc($query)) : null;
    }

    public function getLiveBuses(): array
    {
        if (!$this->tableExists('transport_buses')) {
            return [];
        }

        $items = [];
        $query = mysqli_query($this->conn, $this->liveBusSql('', 'ORDER BY b.bus_number ASC'));

        while ($query && $row = mysqli_fetch_assoc($query)) {
            $items[] = $this->formatLiveBus($row);
        }

        return $items;
    }

    public function userCanViewBus(int $userId, string $role, int $busId): bool
    {
        $role = strtolower($role);
        if ($role === 'administrator') {
            $role = 'admin';
        }

        if ($role === 'admin') {
            return true;
        }

        if ($role === 'driver') {
            $assignment = $this->getDriverAssignmentByUser($userId);
            return (int) ($assignment['bus_id'] ?? 0) === $busId;
        }

        if ($role === 'student') {
            $assignment = $this->getStudentAssignmentByUser($userId);
            return (int) ($assignment['bus_id'] ?? 0) === $busId;
        }

        if ($role === 'parent') {
            foreach ($this->getParentAssignmentsByUser($userId) as $assignment) {
                if ((int) ($assignment['bus_id'] ?? 0) === $busId) {
                    return true;
                }
            }
        }

        return false;
    }

    public function updateDriverLocation(int $userId, ?float $latitude, ?float $longitude, string $status): array
    {
        $allowedStatuses = ['not_started', 'running', 'completed'];
        $status = in_array($status, $allowedStatuses, true) ? $status : 'running';
        $assignment = $this->getDriverAssignmentByUser($userId);
        $busId = (int) ($assignment['bus_id'] ?? 0);

        if ($busId <= 0) {
            return ['success' => false, 'message' => 'No bus assigned'];
        }

        if ($status === 'running') {
            if ($latitude === null || $longitude === null || !$this->validCoordinate($latitude, $longitude)) {
                return ['success' => false, 'message' => 'Waiting for a valid GPS location'];
            }

            mysqli_query(
                $this->conn,
                "INSERT INTO transport_live_location (bus_id, latitude, longitude, status)
                 VALUES ('$busId', '" . (float) $latitude . "', '" . (float) $longitude . "', 'running')
                 ON DUPLICATE KEY UPDATE
                    latitude = VALUES(latitude),
                    longitude = VALUES(longitude),
                    status = VALUES(status),
                    updated_at = NOW()"
            );
        } else {
            mysqli_query(
                $this->conn,
                "INSERT INTO transport_live_location (bus_id, status)
                 VALUES ('$busId', '$status')
                 ON DUPLICATE KEY UPDATE status = VALUES(status), updated_at = NOW()"
            );
        }

        return [
            'success' => true,
            'bus_id' => $busId,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'status' => $status,
            'timestamp' => date('Y-m-d H:i:s'),
        ];
    }

    public function getStudentAssignment(int $student_db_id): ?array
    {
        $items = $this->getAssignments("ba.student_id = " . (int) $student_db_id);
        return $items[0] ?? null;
    }

    public function getParentAssignments(array $children): array
    {
        $assignments = [];
        foreach ($children as $child) {
            $assignment = $this->getStudentAssignment((int) $child['id']);
            $assignments[] = [
                'child' => $child,
                'assignment' => $assignment,
            ];
        }

        return $assignments;
    }

    public function getCurrentRouteStops(int $routeId): array
    {
        if (!$this->tableExists('transport_stops') || $routeId <= 0) {
            return [];
        }

        $items = [];
        $query = mysqli_query(
            $this->conn,
            "SELECT stop_id, stop_name, latitude, longitude, stop_order, is_start, is_end, arrival_time
             FROM transport_stops
             WHERE route_id = '" . (int) $routeId . "'
             ORDER BY stop_order ASC"
        );

        while ($query && $row = mysqli_fetch_assoc($query)) {
            $items[] = [
                'stop_id' => (int) $row['stop_id'],
                'stop_name' => $row['stop_name'],
                'latitude' => $row['latitude'] !== null ? (float) $row['latitude'] : null,
                'longitude' => $row['longitude'] !== null ? (float) $row['longitude'] : null,
                'stop_order' => (int) $row['stop_order'],
                'is_start' => (int) $row['is_start'],
                'is_end' => (int) $row['is_end'],
                'arrival_time' => $row['arrival_time'],
            ];
        }

        return $items;
    }

    public function getReport(string $type): array
    {
        if ($type === 'bus-utilization') {
            return $this->busUtilizationReport();
        }

        if ($type === 'student-assignments') {
            return $this->getAssignments();
        }

        return $this->routeReport();
    }

    public function reportColumns(string $type): array
    {
        if ($type === 'bus-utilization') {
            return ['bus_number', 'registration_number', 'capacity', 'assigned_students', 'usage_percent'];
        }

        if ($type === 'student-assignments') {
            return ['student_name', 'student_code', 'bus_number', 'route_name', 'pickup_stop', 'drop_stop'];
        }

        return ['route_name', 'start_location', 'end_location', 'stop_count', 'assigned_students'];
    }

    private function currentTransportReady(): bool
    {
        foreach (['transport_buses', 'transport_routes', 'transport_stops', 'transport_student_assignments', 'transport_staff', 'transport_live_location'] as $table) {
            if (!$this->tableExists($table)) {
                return false;
            }
        }

        return true;
    }

    private function formatLiveBus(array $row): array
    {
        $stops = $this->getCurrentRouteStops((int) ($row['route_id'] ?? 0));
        $status = $row['tracking_status'] ?: 'not_started';
        $latitude = $row['latitude'] !== null ? (float) $row['latitude'] : null;
        $longitude = $row['longitude'] !== null ? (float) $row['longitude'] : null;
        if ($status === 'not_started') {
            $latitude = null;
            $longitude = null;
        }
        $routeMeta = $this->routeProgressMeta($latitude, $longitude, $stops);

        return [
            'bus_id' => (int) $row['bus_id'],
            'bus_number' => $row['bus_number'],
            'bus_name' => $row['bus_name'],
            'capacity' => (int) ($row['capacity'] ?? 0),
            'latitude' => $latitude,
            'longitude' => $longitude,
            'status' => $status,
            'updated_at' => $row['updated_at'],
            'driver' => [
                'staff_id' => (int) ($row['staff_id'] ?? 0),
                'name' => $row['driver_name'],
                'phone' => $row['driver_phone'],
                'profile_photo' => $row['driver_photo'] ?? null,
            ],
            'route' => [
                'route_id' => (int) ($row['route_id'] ?? 0),
                'route_name' => $row['route_name'],
                'route_color' => $row['route_color'] ?: '#2563eb',
                'start_time' => $row['start_time'],
                'end_time' => $row['end_time'],
            ],
            'stops' => $stops,
            'progress' => $routeMeta,
            'student_count' => $this->tableExists('transport_student_assignments')
                ? $this->scalar("SELECT COUNT(*) FROM transport_student_assignments WHERE bus_id = '" . (int) $row['bus_id'] . "'")
                : 0,
        ];
    }

    private function liveBusSql(string $where = '', string $suffix = ''): string
    {
        $liveSelect = "NULL AS latitude, NULL AS longitude, 'not_started' AS tracking_status, NULL AS updated_at";
        $liveJoin = '';
        if ($this->tableExists('transport_live_location')) {
            $liveSelect = 'll.latitude, ll.longitude, ll.status AS tracking_status, ll.updated_at';
            $liveJoin = 'LEFT JOIN transport_live_location ll ON ll.bus_id = b.bus_id';
        }

        $driverSelect = 'NULL AS staff_id, NULL AS driver_name, NULL AS driver_phone, NULL AS driver_photo';
        $driverJoin = '';
        if ($this->tableExists('transport_staff')) {
            $driverSelect = 'd.staff_id, d.full_name AS driver_name, d.phone AS driver_phone, d.profile_photo AS driver_photo';
            $driverJoin = 'LEFT JOIN transport_staff d ON d.staff_id = b.driver_id';
        }

        $routeSelect = "NULL AS route_id, NULL AS route_name, '#2563eb' AS route_color, b.start_time, b.end_time";
        $routeJoin = '';
        if ($this->tableExists('transport_routes')) {
            $routeSelect = 'r.route_id, r.route_name, r.route_color, COALESCE(r.start_time, b.start_time) AS start_time, COALESCE(r.end_time, b.end_time) AS end_time';
            $routeJoin = 'LEFT JOIN transport_routes r ON r.bus_id = b.bus_id';
        }

        return "SELECT b.bus_id, b.bus_number, b.bus_name, b.capacity,
                    $liveSelect,
                    $driverSelect,
                    $routeSelect
             FROM transport_buses b
             $liveJoin
             $driverJoin
             $routeJoin
             $where
             $suffix";
    }

    private function withRouteMeta(array $row): array
    {
        $bus = $this->formatLiveBus([
            'bus_id' => $row['bus_id'] ?? 0,
            'bus_number' => $row['bus_number'] ?? null,
            'bus_name' => $row['bus_name'] ?? null,
            'capacity' => $row['capacity'] ?? 0,
            'latitude' => $row['latitude'] ?? null,
            'longitude' => $row['longitude'] ?? null,
            'tracking_status' => $row['tracking_status'] ?? ($row['status'] ?? 'not_started'),
            'updated_at' => $row['updated_at'] ?? null,
            'staff_id' => $row['staff_id'] ?? null,
            'driver_name' => $row['driver_name'] ?? ($row['full_name'] ?? null),
            'driver_phone' => $row['driver_phone'] ?? ($row['phone'] ?? null),
            'driver_photo' => $row['driver_photo'] ?? ($row['profile_photo'] ?? null),
            'route_id' => $row['route_id'] ?? 0,
            'route_name' => $row['route_name'] ?? null,
            'route_color' => $row['route_color'] ?? '#2563eb',
            'start_time' => $row['start_time'] ?? null,
            'end_time' => $row['end_time'] ?? null,
        ]);

        return array_merge($row, [
            'tracking_status' => $bus['status'],
            'stops' => $bus['stops'],
            'progress' => $bus['progress'],
            'student_count' => $bus['student_count'],
        ]);
    }

    private function routeProgressMeta(?float $latitude, ?float $longitude, array $stops): array
    {
        $validStops = array_values(array_filter($stops, fn($stop) => $stop['latitude'] !== null && $stop['longitude'] !== null));
        $totalStops = count($validStops);

        if ($totalStops === 0) {
            return [
                'percent' => 0,
                'current_stop' => null,
                'next_stop' => null,
                'eta_minutes' => null,
                'distance_to_next_km' => null,
                'completed_stops' => 0,
                'total_stops' => count($stops),
            ];
        }

        if ($latitude === null || $longitude === null) {
            return [
                'percent' => 0,
                'current_stop' => $validStops[0],
                'next_stop' => $validStops[0],
                'eta_minutes' => null,
                'distance_to_next_km' => null,
                'completed_stops' => 0,
                'total_stops' => count($stops),
            ];
        }

        $nearestIndex = 0;
        $nearestDistance = PHP_FLOAT_MAX;

        foreach ($validStops as $index => $stop) {
            $distance = $this->distanceKm($latitude, $longitude, (float) $stop['latitude'], (float) $stop['longitude']);
            if ($distance < $nearestDistance) {
                $nearestDistance = $distance;
                $nearestIndex = $index;
            }
        }

        $nextIndex = min($totalStops - 1, $nearestDistance <= 0.15 ? $nearestIndex + 1 : $nearestIndex);
        $currentIndex = max(0, min($totalStops - 1, $nextIndex - 1));
        $nextStop = $validStops[$nextIndex] ?? $validStops[$totalStops - 1];
        $distanceToNext = $this->distanceKm($latitude, $longitude, (float) $nextStop['latitude'], (float) $nextStop['longitude']);
        $completed = min($totalStops, $nearestDistance <= 0.15 ? $nearestIndex + 1 : $nearestIndex);
        $percent = $totalStops > 1 ? round(($completed / ($totalStops - 1)) * 100) : 100;
        $etaMinutes = $distanceToNext > 0 ? max(1, (int) ceil(($distanceToNext / 24) * 60)) : 0;

        return [
            'percent' => min(100, max(0, $percent)),
            'current_stop' => $validStops[$currentIndex] ?? null,
            'next_stop' => $nextStop,
            'eta_minutes' => $etaMinutes,
            'distance_to_next_km' => round($distanceToNext, 2),
            'completed_stops' => $completed,
            'total_stops' => count($stops),
        ];
    }

    private function distanceKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    private function validCoordinate(?float $latitude, ?float $longitude): bool
    {
        return $latitude !== null
            && $longitude !== null
            && $latitude >= -90
            && $latitude <= 90
            && $longitude >= -180
            && $longitude <= 180
            && !($latitude == 0.0 && $longitude == 0.0);
    }

    private function getAssignments(string $extraWhere = ''): array
    {
        if (!$this->tableExists('bus_assignments')) {
            return [];
        }

        $where = $extraWhere !== '' ? "WHERE $extraWhere" : '';

        $rows = [];
        $query = mysqli_query(
            $this->conn,
            "SELECT ba.*, st.student_id AS student_code, u.full_name AS student_name,
                              b.bus_number, b.registration_number, b.capacity,
                              d.name AS driver_name, d.phone AS driver_phone,
                              h.name AS helper_name, h.phone AS helper_phone,
                          r.route_code, r.route_name, r.start_point, r.end_point,
                    ps.stop_name AS pickup_stop, ds.stop_name AS drop_stop
             FROM bus_assignments ba
             INNER JOIN students st ON st.id = ba.student_id
             INNER JOIN users u ON u.id = st.user_id
             INNER JOIN buses b ON b.bus_id = ba.bus_id
                      INNER JOIN routes r ON r.route_id = ba.route_id
                          LEFT JOIN drivers d ON d.driver_id = b.driver_id
                          LEFT JOIN helpers h ON h.helper_id = b.helper_id
             LEFT JOIN stops ps ON ps.stop_id = ba.pickup_stop_id
             LEFT JOIN stops ds ON ds.stop_id = ba.drop_stop_id
             $where
             ORDER BY u.full_name ASC"
        );

        while ($query && $row = mysqli_fetch_assoc($query)) {
            $rows[] = $row;
        }

        return $rows;
    }

    private function getRouteStops(): array
    {
        $rows = [];
        $query = mysqli_query(
            $this->conn,
            "SELECT rs.*, r.route_name, s.stop_name
             FROM route_stops rs
             INNER JOIN routes r ON r.route_id = rs.route_id
             INNER JOIN stops s ON s.stop_id = rs.stop_id
             ORDER BY r.route_name ASC, rs.stop_order ASC"
        );

        while ($query && $row = mysqli_fetch_assoc($query)) {
            $rows[] = $row;
        }

        return $rows;
    }

    private function routeReport(): array
    {
        $rows = [];
        $query = mysqli_query(
            $this->conn,
                    "SELECT r.route_name, r.start_location, r.end_location,
                    COUNT(DISTINCT rs.route_stop_id) AS stop_count,
                    COUNT(DISTINCT ba.assignment_id) AS assigned_students
             FROM routes r
             LEFT JOIN route_stops rs ON rs.route_id = r.route_id
             LEFT JOIN bus_assignments ba ON ba.route_id = r.route_id
                     GROUP BY r.route_id, r.route_name, r.start_location, r.end_location
                     ORDER BY r.route_name ASC"
        );

        while ($query && $row = mysqli_fetch_assoc($query)) {
            $rows[] = $row;
        }

        return $rows;
    }

    private function busUtilizationReport(): array
    {
        $rows = [];
        $query = mysqli_query(
            $this->conn,
            "SELECT b.bus_number, b.registration_number, b.capacity,
                    COUNT(ba.assignment_id) AS assigned_students
             FROM buses b
             LEFT JOIN bus_assignments ba ON ba.bus_id = b.bus_id
             GROUP BY b.bus_id, b.bus_number, b.registration_number, b.capacity
             ORDER BY b.bus_number ASC"
        );

        while ($query && $row = mysqli_fetch_assoc($query)) {
            $capacity = max(1, (int) $row['capacity']);
            $row['usage_percent'] = round(((int) $row['assigned_students'] / $capacity) * 100, 2) . '%';
            $rows[] = $row;
        }

        return $rows;
    }

    private function prepareValues(string $module_key, array $module, array $data): array
    {
        $values = [];

        foreach ($module['fields'] as $column => $field) {
            if (!$this->hasColumn($module['table'], $column)) {
                continue;
            }

            $value = trim((string) ($data[$column] ?? ''));
            if (($field['type'] ?? '') === 'datetime-local' && $value !== '') {
                $value = str_replace('T', ' ', $value) . ':00';
            }

            if (($field['type'] ?? '') === 'number' && $value === '') {
                $value = null;
            }

            if (($field['type'] ?? '') === 'select' && $value === '') {
                $options = array_keys($field['options'] ?? []);
                $value = $options[0] ?? 'active';
            }

            $values[$column] = $value;
        }

        if ($module_key === 'assignments' && $this->hasColumn('bus_assignments', 'status')) {
            $values['status'] = 'active';
        }

        return $values;
    }

    private function normalizeRow(string $module_key, array $row): array
    {
        return $row;
    }

    private function optionRows(string $table, string $idColumn, string $labelExpression): array
    {
        if (!$this->tableExists($table)) {
            return [];
        }

        $rows = [];
        $query = mysqli_query(
            $this->conn,
            "SELECT $idColumn AS id, $labelExpression AS label FROM $table ORDER BY label ASC"
        );

        while ($query && $row = mysqli_fetch_assoc($query)) {
            $rows[] = $row;
        }

        return $rows;
    }

    private function resolveOrder(array $module): string
    {
        return $module['order'];
    }

    private function sqlValue($value): string
    {
        if ($value === null || $value === '') {
            return 'NULL';
        }

        return "'" . mysqli_real_escape_string($this->conn, (string) $value) . "'";
    }

    private function scalar(string $sql): int
    {
        $result = mysqli_query($this->conn, $sql);
        $row = $result ? mysqli_fetch_row($result) : [0];

        return (int) ($row[0] ?? 0);
    }

    public function tableExists(string $table): bool
    {
        $table = mysqli_real_escape_string($this->conn, $table);
        $result = mysqli_query($this->conn, "SHOW TABLES LIKE '$table'");

        return $result && mysqli_num_rows($result) > 0;
    }

    public function hasColumn(string $table, string $column): bool
    {
        $key = $table . '.' . $column;
        if (array_key_exists($key, $this->columnCache)) {
            return $this->columnCache[$key];
        }

        $table = mysqli_real_escape_string($this->conn, $table);
        $column = mysqli_real_escape_string($this->conn, $column);
        $result = mysqli_query($this->conn, "SHOW COLUMNS FROM `$table` LIKE '$column'");
        $this->columnCache[$key] = $result && mysqli_num_rows($result) > 0;

        return $this->columnCache[$key];
    }
}
