<?php 
require_once 'db_config.php';
checkAuth();
$user = getCurrentUser();
$isAdmin = ($user['role'] === 'admin');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Банк Консультант - Управление заявками</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="light">
    <div class="app">
        <!-- ЛЕВОЕ МЕНЮ -->
        <div class="sidebar">
         <div class="sidebar-header">
    <div class="logo-container">
        <img src="images/logotip.png" alt="Банк Консультант" class="sidebar-logo">
    </div>
</div>
            <div class="sidebar-nav">
                <div class="nav-item active" data-page="requests">📋 <span data-lang="menu_requests">Заявки</span></div>
                <?php if($isAdmin): ?>
                <div class="nav-item" data-page="employees">👥 <span data-lang="menu_employees">Сотрудники</span></div>
                <?php endif; ?>
                <div class="nav-item" data-page="clients">👤 <span data-lang="menu_clients">Клиенты</span></div>
               
                <div class="nav-item" data-page="reports">📊 <span data-lang="menu_reports">Отчеты</span></div>
                <div class="nav-item" data-page="settings">⚙️ <span data-lang="menu_settings">Настройки</span></div>
            </div>
        </div>
        
        <!-- ОСНОВНАЯ ОБЛАСТЬ -->
        <div class="main-content">
            <!-- Верхняя панель -->
            <div class="top-bar">
                <div class="search-box">
                    <input type="text" id="searchInput" data-lang-placeholder="search_placeholder" placeholder="🔍 Поиск...">
                </div>
                <div class="top-bar-actions">
                    <span class="user-name"><?= htmlspecialchars($user['full_name']) ?></span>
                    <span class="user-role">(<?= $user['role'] === 'admin' ? 'Администратор' : 'Консультант' ?>)</span>
                    <button class="btn-icon" id="themeToggle" title="Сменить тему">🌓</button>
                    <button class="btn-icon" id="langToggle" title="Сменить язык">🌐</button>
                    <button class="btn-primary" id="newItemBtn">+ <span data-lang="btn_new_request">Новая заявка</span></button>
                    <a href="logout.php" class="logout-link" data-lang="btn_logout">Выход</a>
                </div>
            </div>
            
            <!-- Область с заявками -->
            <div class="requests-layout" id="requestsLayout">
                <!-- Левая панель категорий -->
                
<!-- Левая панель категорий -->
<div class="categories-panel">
    <div class="category-group">
        <div class="category-header" data-lang="category_my">МОИ</div>
        <div class="category-item active" data-filter="my">
            <span data-lang="category_my_requests">Мои заявки</span>
            <span class="category-count" id="myCount">0</span>
        </div>
        <div class="category-item" data-filter="overdue">
            <span data-lang="category_overdue">Просроченные заявки</span>
            <span class="category-count" id="overdueCount">0</span>
        </div>
    </div>
    
    <div class="category-group">
        <div class="category-header" data-lang="category_common">ОБЩИЕ</div>
        <div class="category-item" data-filter="all">
            <span data-lang="category_all_except_closed">Все (кроме закрытых)</span>
            <span class="category-count" id="allCount">0</span>
        </div>
        <div class="category-item" data-filter="open">
            <span data-lang="category_open">Открытые</span>
            <span class="category-count" id="openCount">0</span>
        </div>
        <div class="category-item" data-filter="in_progress">
            <span data-lang="category_in_progress">В работе</span>
            <span class="category-count" id="progressCount">0</span>
        </div>
        <div class="category-item" data-filter="postponed">
            <span data-lang="category_postponed">Отложенные</span>
            <span class="category-count" id="postponedCount">0</span>
        </div>
        <div class="category-item" data-filter="deal">
            <span data-lang="category_deals">Сделки</span>
            <span class="category-count" id="dealCount">0</span>
        </div>
    </div>
</div>
                
                <!-- Правая панель с таблицей -->
                <div class="requests-panel">
                    <table class="requests-table" id="requestsTable">
                        <thead>
                            <tr>
                                <th data-lang="table_number">№</th>
                                <th data-lang="table_title">Название</th>
                                <th data-lang="table_status">Статус</th>
                                <th data-lang="table_priority">Приоритет</th>
                                <th data-lang="table_executor">Исполнители</th>
                                <th data-lang="table_client">Клиент</th>
                                <th data-lang="table_deadline">Срок выполнения</th>
                                <th data-lang="table_updated">Дата изменения</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="requestsTableBody">
                            <tr><td colspan="9" style="text-align: center; padding: 40px;">Загрузка...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Контейнер для других страниц -->
            <div id="otherPages" style="display: none; padding: 20px;"></div>
        </div>
    </div>
    
    <!-- Модальное окно для заявки -->
    <div class="modal" id="requestModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle" data-lang="modal_new">Новая заявка</h3>
                <button class="modal-close" onclick="closeRequestModal()">&times;</button>
            </div>
            <form id="requestForm">
                <div class="modal-body">
                    <input type="hidden" name="id" id="reqId">
                    
                    <div class="form-group">
                        <label data-lang="modal_client">Клиент</label>
                        <select name="client_id" id="clientSelect" class="form-control" required>
                            <option value="" data-lang="modal_select_client">Выберите клиента</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label data-lang="modal_topic">Тема (Название)</label>
                        <input type="text" name="topic" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label data-lang="modal_description">Описание</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label data-lang="modal_status">Статус</label>
                        <select name="status" class="form-control" required>
                            <option value="open" data-lang="status_open">Открыта</option>
                            <option value="in_progress" data-lang="status_in_progress">В работе</option>
                            <option value="postponed" data-lang="status_postponed">Отложена</option>
                            <option value="deal" data-lang="status_deal">Сделка (заключен договор)</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label data-lang="modal_priority">Приоритет</label>
                        <select name="priority" class="form-control">
                            <option value="medium" data-lang="priority_medium">Средний</option>
                            <option value="high" data-lang="priority_high">Высокий</option>
                            <option value="low" data-lang="priority_low">Низкий</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label data-lang="modal_deadline">Срок выполнения</label>
                        <input type="datetime-local" name="deadline" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeRequestModal()" data-lang="btn_cancel">Отмена</button>
                    <button type="submit" class="btn-primary" data-lang="btn_save">Сохранить</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Модальное окно для клиента -->
    <div class="modal" id="clientModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="clientModalTitle">Новый клиент</h3>
                <button class="modal-close" onclick="closeClientModal()">&times;</button>
            </div>
            <form id="clientForm">
                <div class="modal-body">
                    <input type="hidden" name="id" id="clientId">
                    
                    <div class="form-group">
                        <label>ФИО</label>
                        <input type="text" name="full_name" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Телефон</label>
                        <input type="text" name="phone" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label>Паспорт</label>
                        <input type="text" name="passport" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeClientModal()">Отмена</button>
                    <button type="submit" class="btn-primary">Сохранить</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Модальное окно для сотрудника -->
    <div class="modal" id="employeeModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="employeeModalTitle">Новый сотрудник</h3>
                <button class="modal-close" onclick="closeEmployeeModal()">&times;</button>
            </div>
            <form id="employeeForm">
                <div class="modal-body">
                    <input type="hidden" name="id" id="employeeId">
                    
                    <div class="form-group">
                        <label>Логин</label>
                        <input type="text" name="login" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Пароль</label>
                        <input type="password" name="password" class="form-control" id="employeePassword">
                        <small style="color: #999;">Оставьте пустым, чтобы не менять</small>
                    </div>
                    
                    <div class="form-group">
                        <label>ФИО</label>
                        <input type="text" name="full_name" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Роль</label>
                        <select name="role" class="form-control" required>
                            <option value="consultant">Консультант</option>
                            <option value="admin">Администратор</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeEmployeeModal()">Отмена</button>
                    <button type="submit" class="btn-primary">Сохранить</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    // ============================================
    // ЯЗЫКОВЫЕ НАСТРОЙКИ
    // ============================================
    const translations = {
        ru: {
            menu_knowledge: 'База знаний',
            menu_requests: 'Заявки',
            menu_employees: 'Сотрудники',
            menu_clients: 'Клиенты',
            menu_map: 'Карта',
            menu_assets: 'Активы',
            menu_reports: 'Отчеты',
            menu_settings: 'Настройки',
            category_my: 'МОИ',
            category_my_requests: 'Мои заявки',
            category_overdue: 'Просроченные заявки',
            category_common: 'ОБЩИЕ',
            category_all_except_closed: 'Все (кроме закрытых)',
            category_open: 'Открытые',
            category_in_progress: 'В работе',
            category_postponed: 'Отложенные',
            category_deals: 'Сделки',
            table_number: '№',
            table_title: 'Название',
            table_status: 'Статус',
            table_priority: 'Приоритет',
            table_executor: 'Исполнители',
            table_client: 'Клиент',
            table_deadline: 'Срок выполнения',
            table_updated: 'Дата изменения',
            table_actions: 'Действия',
            status_open: 'Открыта',
            status_in_progress: 'В работе',
            status_postponed: 'Отложена',
            status_deal: 'Сделка',
            priority_high: 'Высокий',
            priority_medium: 'Средний',
            priority_low: 'Низкий',
            btn_new_request: 'Новая заявка',
            btn_new_client: 'Новый клиент',
            btn_new_employee: 'Новый сотрудник',
            btn_logout: 'Выход',
            btn_save: 'Сохранить',
            btn_cancel: 'Отмена',
            btn_apply: 'Применить',
            btn_edit: 'Редактировать',
            btn_delete: 'Удалить',
            search_placeholder: '🔍 Поиск...',
            modal_new_request: 'Новая заявка',
            modal_edit_request: 'Редактирование заявки №',
            modal_new_client: 'Новый клиент',
            modal_edit_client: 'Редактирование клиента',
            modal_new_employee: 'Новый сотрудник',
            modal_edit_employee: 'Редактирование сотрудника',
            modal_client: 'Клиент',
            modal_topic: 'Тема (Название)',
            modal_description: 'Описание',
            modal_status: 'Статус',
            modal_priority: 'Приоритет',
            modal_deadline: 'Срок выполнения',
            modal_select_client: 'Выберите клиента',
            settings_title: 'Настройки',
            settings_theme: 'Тема оформления',
            settings_theme_light: 'Светлая',
            settings_theme_dark: 'Темная',
            settings_language: 'Язык интерфейса',
            settings_lang_ru: 'Русский',
            settings_lang_en: 'English',
            reports_title: 'Отчеты',
            reports_open: 'Открытых заявок',
            reports_in_progress: 'В работе',
            reports_overdue: 'Просрочено',
            reports_avg_time: 'Среднее время (дней)',
            clients_title: 'Клиенты',
            employees_title: 'Сотрудники',
            loading: 'Загрузка...',
            no_data: 'Нет данных',
            today: 'Сегодня',
            yesterday: 'Вчера',
            save_success: 'Сохранено успешно',
            delete_confirm: 'Вы уверены, что хотите удалить?',
            delete_success: 'Удалено успешно'
        },
        en: {
            menu_knowledge: 'Knowledge Base',
            menu_requests: 'Requests',
            menu_employees: 'Employees',
            menu_clients: 'Clients',
            menu_map: 'Map',
            menu_assets: 'Assets',
            menu_reports: 'Reports',
            menu_settings: 'Settings',
            category_my: 'MY',
            category_my_requests: 'My Requests',
            category_overdue: 'Overdue',
            category_common: 'COMMON',
            category_all_except_closed: 'All (except closed)',
            category_open: 'Open',
            category_in_progress: 'In Progress',
            category_postponed: 'Postponed',
            category_deals: 'Deals',
            table_number: '#',
            table_title: 'Title',
            table_status: 'Status',
            table_priority: 'Priority',
            table_executor: 'Assignee',
            table_client: 'Client',
            table_deadline: 'Deadline',
            table_updated: 'Updated',
            table_actions: 'Actions',
            status_open: 'Open',
            status_in_progress: 'In Progress',
            status_postponed: 'Postponed',
            status_deal: 'Deal',
            priority_high: 'High',
            priority_medium: 'Medium',
            priority_low: 'Low',
            btn_new_request: 'New Request',
            btn_new_client: 'New Client',
            btn_new_employee: 'New Employee',
            btn_logout: 'Logout',
            btn_save: 'Save',
            btn_cancel: 'Cancel',
            btn_apply: 'Apply',
            btn_edit: 'Edit',
            btn_delete: 'Delete',
            search_placeholder: '🔍 Search...',
            modal_new_request: 'New Request',
            modal_edit_request: 'Edit Request #',
            modal_new_client: 'New Client',
            modal_edit_client: 'Edit Client',
            modal_new_employee: 'New Employee',
            modal_edit_employee: 'Edit Employee',
            modal_client: 'Client',
            modal_topic: 'Topic',
            modal_description: 'Description',
            modal_status: 'Status',
            modal_priority: 'Priority',
            modal_deadline: 'Deadline',
            modal_select_client: 'Select client',
            settings_title: 'Settings',
            settings_theme: 'Theme',
            settings_theme_light: 'Light',
            settings_theme_dark: 'Dark',
            settings_language: 'Language',
            settings_lang_ru: 'Русский',
            settings_lang_en: 'English',
            reports_title: 'Reports',
            reports_open: 'Open Requests',
            reports_in_progress: 'In Progress',
            reports_overdue: 'Overdue',
            reports_avg_time: 'Avg Time (days)',
            clients_title: 'Clients',
            employees_title: 'Employees',
            loading: 'Loading...',
            no_data: 'No data',
            today: 'Today',
            yesterday: 'Yesterday',
            save_success: 'Saved successfully',
            delete_confirm: 'Are you sure you want to delete?',
            delete_success: 'Deleted successfully'
        }
    };

    // Глобальные переменные
    let currentLang = localStorage.getItem('language') || 'ru';
    let currentTheme = localStorage.getItem('theme') || 'light';
    let currentFilter = 'my';
    let currentPage = 'requests';
    let requests = [];
    let clients = [];
    let employees = [];
    const isAdmin = <?= $isAdmin ? 'true' : 'false' ?>;
    const currentUserId = <?= $user['id'] ?>;

    // ============================================
    // ФУНКЦИИ ЯЗЫКА И ТЕМЫ
    // ============================================
    function t(key) {
        return translations[currentLang]?.[key] || key;
    }

    function applyLanguage(lang) {
        currentLang = lang;
        localStorage.setItem('language', lang);
        
        document.querySelectorAll('[data-lang]').forEach(el => {
            const key = el.getAttribute('data-lang');
            if (translations[lang]?.[key]) {
                el.textContent = translations[lang][key];
            }
        });
        
        document.querySelectorAll('[data-lang-placeholder]').forEach(el => {
            const key = el.getAttribute('data-lang-placeholder');
            if (translations[lang]?.[key]) {
                el.placeholder = translations[lang][key];
            }
        });
        
        updateNewButtonText();
        if (currentPage === 'requests') renderTable(requests);
        else if (currentPage === 'clients') renderClientsTable(clients);
        else if (currentPage === 'employees') renderEmployeesTable(employees);
    }

    function applyTheme(theme) {
        currentTheme = theme;
        document.body.className = theme;
        localStorage.setItem('theme', theme);
    }

    function updateNewButtonText() {
        const btn = document.getElementById('newItemBtn');
        if (currentPage === 'requests') {
            btn.innerHTML = `+ <span data-lang="btn_new_request">${t('btn_new_request')}</span>`;
        } else if (currentPage === 'clients') {
            btn.innerHTML = `+ <span data-lang="btn_new_client">${t('btn_new_client')}</span>`;
        } else if (currentPage === 'employees') {
            btn.innerHTML = `+ <span data-lang="btn_new_employee">${t('btn_new_employee')}</span>`;
        }
    }

    // ============================================
    // ИНИЦИАЛИЗАЦИЯ
    // ============================================
    document.addEventListener('DOMContentLoaded', () => {
        applyTheme(currentTheme);
        applyLanguage(currentLang);
        loadRequests();
        setupEventListeners();
    });

    function setupEventListeners() {
        // Навигация
        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', () => {
                const page = item.dataset.page;
                
                document.querySelectorAll('.nav-item').forEach(i => i.classList.remove('active'));
                item.classList.add('active');
                
                currentPage = page;
                updateNewButtonText();
                
                if (page === 'requests') {
                    document.getElementById('requestsLayout').style.display = 'flex';
                    document.getElementById('otherPages').style.display = 'none';
                    document.getElementById('searchInput').style.display = 'block';
                    loadRequests();
                } else {
                    document.getElementById('requestsLayout').style.display = 'none';
                    document.getElementById('otherPages').style.display = 'block';
                    document.getElementById('searchInput').style.display = 'none';
                    loadPage(page);
                }
            });
        });
        
        // Фильтры
        document.querySelectorAll('.category-item').forEach(item => {
            item.addEventListener('click', () => {
                document.querySelectorAll('.category-item').forEach(i => i.classList.remove('active'));
                item.classList.add('active');
                currentFilter = item.dataset.filter;
                filterRequests();
            });
        });
        
        document.getElementById('searchInput').addEventListener('input', filterRequests);
        
        // Кнопки
        document.getElementById('themeToggle').addEventListener('click', () => {
            applyTheme(currentTheme === 'light' ? 'dark' : 'light');
        });
        
        document.getElementById('langToggle').addEventListener('click', () => {
            applyLanguage(currentLang === 'ru' ? 'en' : 'ru');
            if (currentPage !== 'requests') loadPage(currentPage);
        });
        
        document.getElementById('newItemBtn').addEventListener('click', () => {
            if (currentPage === 'requests') openNewRequestModal();
            else if (currentPage === 'clients') openNewClientModal();
            else if (currentPage === 'employees') openNewEmployeeModal();
        });
        
        // Формы
        document.getElementById('requestForm').addEventListener('submit', saveRequest);
        document.getElementById('clientForm').addEventListener('submit', saveClient);
        document.getElementById('employeeForm').addEventListener('submit', saveEmployee);
        
        // Закрытие модалок
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.style.display = 'none';
                }
            });
        });
    }

    // ============================================
    // ЗАГРУЗКА ДАННЫХ
    // ============================================
    async function loadRequests() {
        try {
            const res = await fetch('api/requests.php');
            requests = await res.json();
            updateCounters();
            filterRequests();
        } catch (e) {
            console.error('Ошибка загрузки заявок:', e);
        }
    }

    async function loadClients() {
        try {
            const res = await fetch('api/clients.php');
            clients = await res.json();
            updateClientSelect();
            return clients;
        } catch (e) {
            console.error('Ошибка загрузки клиентов:', e);
            return [];
        }
    }

    async function loadEmployees() {
        try {
            const res = await fetch('api/users.php');
            employees = await res.json();
            return employees;
        } catch (e) {
            console.error('Ошибка загрузки сотрудников:', e);
            return [];
        }
    }

    function updateClientSelect() {
        const select = document.getElementById('clientSelect');
        select.innerHTML = `<option value="">${t('modal_select_client')}</option>`;
        clients.forEach(c => {
            select.innerHTML += `<option value="${c.id}">${c.full_name}</option>`;
        });
    }

    // ============================================
    // ЗАЯВКИ
    // ============================================
    function updateCounters() {
        const counts = { my: 0, overdue: 0, all: 0, open: 0, progress: 0, postponed: 0, deal: 0 };
        const today = new Date();
        
        requests.forEach(req => {
            if (req.status !== 'deal') counts.all++;
            if (req.status === 'open') counts.open++;
            if (req.status === 'in_progress') counts.progress++;
            if (req.status === 'postponed') counts.postponed++;
            if (req.status === 'deal') counts.deal++;
            if (req.deadline && new Date(req.deadline) < today && req.status !== 'deal') counts.overdue++;
            counts.my++;
        });
        
        document.getElementById('myCount').textContent = counts.my;
        document.getElementById('overdueCount').textContent = counts.overdue;
        document.getElementById('allCount').textContent = counts.all;
        document.getElementById('openCount').textContent = counts.open;
        document.getElementById('progressCount').textContent = counts.progress;
        document.getElementById('postponedCount').textContent = counts.postponed;
        document.getElementById('dealCount').textContent = counts.deal;
    }

    function filterRequests() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        const today = new Date();
        
        let filtered = [...requests];
        
        switch(currentFilter) {
            case 'overdue':
                filtered = filtered.filter(r => r.deadline && new Date(r.deadline) < today && r.status !== 'deal');
                break;
            case 'all':
                filtered = filtered.filter(r => r.status !== 'deal');
                break;
            case 'open':
                filtered = filtered.filter(r => r.status === 'open');
                break;
            case 'in_progress':
                filtered = filtered.filter(r => r.status === 'in_progress');
                break;
            case 'postponed':
                filtered = filtered.filter(r => r.status === 'postponed');
                break;
            case 'deal':
                filtered = filtered.filter(r => r.status === 'deal');
                break;
        }
        
        if (searchTerm) {
            filtered = filtered.filter(r => 
                (r.topic || '').toLowerCase().includes(searchTerm) ||
                (r.client_name || '').toLowerCase().includes(searchTerm) ||
                (r.consultant_name || '').toLowerCase().includes(searchTerm)
            );
        }
        
        renderTable(filtered);
    }

    function renderTable(data) {
        const tbody = document.getElementById('requestsTableBody');
        
        if (!data.length) {
            tbody.innerHTML = `<tr><td colspan="9" style="text-align: center; padding: 40px;">${t('no_data')}</td></tr>`;
            return;
        }
        
        tbody.innerHTML = data.map(req => {
            const isOverdue = req.deadline && new Date(req.deadline) < new Date() && req.status !== 'deal';
            
            let statusClass = 'status-badge ';
            if (req.status === 'open') statusClass += 'status-open';
            else if (req.status === 'in_progress') statusClass += 'status-progress';
            else if (req.status === 'postponed') statusClass += 'status-postponed';
            else if (req.status === 'deal') statusClass += 'status-deal';
            
            let priorityClass = '';
            if (req.priority === 'high') priorityClass = 'priority-high';
            else if (req.priority === 'low') priorityClass = 'priority-low';
            else priorityClass = 'priority-medium';
            
            return `
                <tr>
                    <td>${req.id}</td>
                    <td style="cursor: pointer;" onclick="editRequest(${req.id})">${escapeHtml(req.topic || '')}</td>
                    <td><span class="${statusClass}">${t('status_' + req.status) || req.status}</span></td>
                    <td class="${priorityClass}">${t('priority_' + (req.priority || 'medium')) || req.priority || 'Средний'}</td>
                    <td>${escapeHtml(req.consultant_name || '—')}</td>
                    <td>${escapeHtml(req.client_name || '—')}</td>
                    <td class="${isOverdue ? 'deadline-overdue' : ''}">${req.deadline ? formatDate(req.deadline) : '—'}</td>
                    <td>${formatRelativeTime(req.updated_at || req.created_at)}</td>
                    <td>
                        <button class="btn-icon" onclick="editRequest(${req.id})" title="${t('btn_edit')}">✏️</button>
                        <button class="btn-icon" onclick="deleteRequest(${req.id})" title="${t('btn_delete')}" style="color: #e74c3c;">🗑️</button>
                    </td>
                </tr>
            `;
        }).join('');
    }

    function openNewRequestModal() {
        document.getElementById('modalTitle').textContent = t('modal_new_request');
        document.getElementById('reqId').value = '';
        document.getElementById('requestForm').reset();
        document.getElementById('requestModal').style.display = 'flex';
    }

    async function editRequest(id) {
        const req = requests.find(r => r.id == id);
        if (!req) return;
        
        await loadClients();
        
        document.getElementById('modalTitle').textContent = t('modal_edit_request') + id;
        document.getElementById('reqId').value = req.id;
        document.getElementById('clientSelect').value = req.client_id;
        document.querySelector('[name="topic"]').value = req.topic || '';
        document.querySelector('[name="description"]').value = req.description || '';
        document.querySelector('[name="status"]').value = req.status || 'open';
        document.querySelector('[name="priority"]').value = req.priority || 'medium';
        
        if (req.deadline) {
            document.querySelector('[name="deadline"]').value = new Date(req.deadline).toISOString().slice(0, 16);
        } else {
            document.querySelector('[name="deadline"]').value = '';
        }
        
        document.getElementById('requestModal').style.display = 'flex';
    }

    async function deleteRequest(id) {
        if (!confirm(t('delete_confirm'))) return;
        
        try {
            await fetch(`api/requests.php?id=${id}`, { method: 'DELETE' });
            await loadRequests();
        } catch (e) {
            alert('Ошибка удаления');
        }
    }

    async function saveRequest(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    
    try {
        const res = await fetch('api/requests.php', { 
            method: 'POST', 
            body: formData 
        });
        const data = await res.json();
        
        if (data.success) {
            closeRequestModal();
            await loadRequests();
        } else {
            alert(data.message || 'Ошибка сохранения');
        }
    } catch (e) {
        console.error('Ошибка:', e);
        alert('Ошибка сохранения');
    }
}

    function closeRequestModal() {
        document.getElementById('requestModal').style.display = 'none';
    }

    // ============================================
    // КЛИЕНТЫ
    // ============================================
    function renderClientsTable(data) {
        const container = document.getElementById('otherPages');
        
        container.innerHTML = `
            <h2 data-lang="clients_title">${t('clients_title')}</h2>
            <table class="requests-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>ФИО</th>
                        <th>Телефон</th>
                        <th>Email</th>
                        <th>Паспорт</th>
                        <th>${t('table_actions')}</th>
                    </tr>
                </thead>
                <tbody>
                    ${data.map(c => `
                        <tr>
                            <td>${c.id}</td>
                            <td>${escapeHtml(c.full_name)}</td>
                            <td>${escapeHtml(c.phone)}</td>
                            <td>${escapeHtml(c.email || '—')}</td>
                            <td>${escapeHtml(c.passport || '—')}</td>
                            <td>
                                <button class="btn-icon" onclick="editClient(${c.id})" title="${t('btn_edit')}">✏️</button>
                                <button class="btn-icon" onclick="deleteClient(${c.id})" title="${t('btn_delete')}" style="color: #e74c3c;">🗑️</button>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        `;
    }

    function openNewClientModal() {
        document.getElementById('clientModalTitle').textContent = t('modal_new_client');
        document.getElementById('clientId').value = '';
        document.getElementById('clientForm').reset();
        document.getElementById('clientModal').style.display = 'flex';
    }

    function editClient(id) {
        const client = clients.find(c => c.id == id);
        if (!client) return;
        
        document.getElementById('clientModalTitle').textContent = t('modal_edit_client');
        document.getElementById('clientId').value = client.id;
        document.querySelector('#clientForm [name="full_name"]').value = client.full_name;
        document.querySelector('#clientForm [name="phone"]').value = client.phone;
        document.querySelector('#clientForm [name="email"]').value = client.email || '';
        document.querySelector('#clientForm [name="passport"]').value = client.passport || '';
        
        document.getElementById('clientModal').style.display = 'flex';
    }

    async function deleteClient(id) {
        if (!confirm(t('delete_confirm'))) return;
        
        try {
            await fetch(`api/clients.php?id=${id}`, { method: 'DELETE' });
            await loadPage('clients');
            await loadClients();
        } catch (e) {
            alert('Ошибка удаления');
        }
    }

    async function saveClient(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    
    // Всегда используем POST
    try {
        const res = await fetch('api/clients.php', { 
            method: 'POST', 
            body: formData 
        });
        const data = await res.json();
        
        if (data.success) {
            closeClientModal();
            await loadPage('clients');
            await loadClients();
            alert(t('save_success'));
        } else {
            alert(data.message || 'Ошибка сохранения');
        }
    } catch (e) {
        console.error('Ошибка:', e);
        alert('Ошибка сохранения: ' + e.message);
    }
}
    function closeClientModal() {
        document.getElementById('clientModal').style.display = 'none';
    }

    // ============================================
    // СОТРУДНИКИ
    // ============================================
    function renderEmployeesTable(data) {
        const container = document.getElementById('otherPages');
        
        container.innerHTML = `
            <h2 data-lang="employees_title">${t('employees_title')}</h2>
            <table class="requests-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Логин</th>
                        <th>ФИО</th>
                        <th>Роль</th>
                        <th>${t('table_actions')}</th>
                    </tr>
                </thead>
                <tbody>
                    ${data.map(e => `
                        <tr>
                            <td>${e.id}</td>
                            <td>${escapeHtml(e.login)}</td>
                            <td>${escapeHtml(e.full_name)}</td>
                            <td>${e.role === 'admin' ? 'Администратор' : 'Консультант'}</td>
                            <td>
                                <button class="btn-icon" onclick="editEmployee(${e.id})" title="${t('btn_edit')}">✏️</button>
                                ${e.role !== 'admin' ? `<button class="btn-icon" onclick="deleteEmployee(${e.id})" title="${t('btn_delete')}" style="color: #e74c3c;">🗑️</button>` : ''}
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        `;
    }

    function openNewEmployeeModal() {
        document.getElementById('employeeModalTitle').textContent = t('modal_new_employee');
        document.getElementById('employeeId').value = '';
        document.getElementById('employeeForm').reset();
        document.getElementById('employeePassword').required = true;
        document.getElementById('employeeModal').style.display = 'flex';
    }

    function editEmployee(id) {
        const employee = employees.find(e => e.id == id);
        if (!employee) return;
        
        document.getElementById('employeeModalTitle').textContent = t('modal_edit_employee');
        document.getElementById('employeeId').value = employee.id;
        document.querySelector('#employeeForm [name="login"]').value = employee.login;
        document.querySelector('#employeeForm [name="full_name"]').value = employee.full_name;
        document.querySelector('#employeeForm [name="role"]').value = employee.role;
        document.getElementById('employeePassword').required = false;
        document.getElementById('employeePassword').value = '';
        
        document.getElementById('employeeModal').style.display = 'flex';
    }

    async function deleteEmployee(id) {
        if (!confirm(t('delete_confirm'))) return;
        
        try {
            await fetch(`api/users.php?id=${id}`, { method: 'DELETE' });
            await loadPage('employees');
        } catch (e) {
            alert('Ошибка удаления');
        }
    }

    async function saveEmployee(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const id = formData.get('id');
    
    // Всегда используем POST
    try {
        const res = await fetch('api/users.php', { 
            method: 'POST', 
            body: formData 
        });
        const data = await res.json();
        
        if (data.success) {
            closeEmployeeModal();
            await loadPage('employees');
            alert(t('save_success'));
        } else {
            alert(data.message || 'Ошибка сохранения');
        }
    } catch (e) {
        console.error('Ошибка:', e);
        alert('Ошибка сохранения: ' + e.message);
    }
}

    function closeEmployeeModal() {
        document.getElementById('employeeModal').style.display = 'none';
    }

    // ============================================
    // ЗАГРУЗКА СТРАНИЦ
    // ============================================
    async function loadPage(page) {
        if (page === 'reports') {
            const res = await fetch('api/reports_data.php');
            const data = await res.json();
            
            document.getElementById('otherPages').innerHTML = `
                <h2 data-lang="reports_title">${t('reports_title')}</h2>
                <div class="stats-row">
                    <div class="stat-card">
                        <div class="stat-value">${data.statuses?.open || 0}</div>
                        <div class="stat-label" data-lang="reports_open">${t('reports_open')}</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">${data.in_progress || 0}</div>
                        <div class="stat-label" data-lang="reports_in_progress">${t('reports_in_progress')}</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value" style="color: #d32f2f;">${data.overdue || 0}</div>
                        <div class="stat-label" data-lang="reports_overdue">${t('reports_overdue')}</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">${data.avg_completion_days || 0}</div>
                        <div class="stat-label" data-lang="reports_avg_time">${t('reports_avg_time')}</div>
                    </div>
                </div>
            `;
        } else if (page === 'settings') {
            document.getElementById('otherPages').innerHTML = `
                <h2 data-lang="settings_title">${t('settings_title')}</h2>
                <div class="settings-section">
                    <div class="setting-item">
                        <span data-lang="settings_theme">${t('settings_theme')}</span>
                        <select id="themeSelect" class="form-control" style="width: auto;">
                            <option value="light" ${currentTheme === 'light' ? 'selected' : ''}>${t('settings_theme_light')}</option>
                            <option value="dark" ${currentTheme === 'dark' ? 'selected' : ''}>${t('settings_theme_dark')}</option>
                        </select>
                    </div>
                    <div class="setting-item">
                        <span data-lang="settings_language">${t('settings_language')}</span>
                        <select id="languageSelect" class="form-control" style="width: auto;">
                            <option value="ru" ${currentLang === 'ru' ? 'selected' : ''}>${t('settings_lang_ru')}</option>
                            <option value="en" ${currentLang === 'en' ? 'selected' : ''}>${t('settings_lang_en')}</option>
                        </select>
                    </div>
                    <button class="btn-primary" onclick="applySettingsFromPage()">${t('btn_apply')}</button>
                </div>
            `;
        } else if (page === 'clients') {
            const data = await loadClients();
            renderClientsTable(data);
        } else if (page === 'employees' && isAdmin) {
            const data = await loadEmployees();
            renderEmployeesTable(data);
        } else {
            document.getElementById('otherPages').innerHTML = `<h2>${page} - ${t('loading')}</h2>`;
        }
    }

    function applySettingsFromPage() {
        const themeSelect = document.getElementById('themeSelect');
        const langSelect = document.getElementById('languageSelect');
        
        if (themeSelect) applyTheme(themeSelect.value);
        if (langSelect) applyLanguage(langSelect.value);
        
        alert(t('save_success'));
        loadPage('settings');
    }

    // ============================================
    // ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ
    // ============================================
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function formatDate(dateStr) {
        const date = new Date(dateStr);
        return date.toLocaleString(currentLang === 'ru' ? 'ru-RU' : 'en-US', {
            day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit'
        });
    }

    function formatRelativeTime(dateStr) {
        const date = new Date(dateStr);
        const now = new Date();
        const diff = now - date;
        
        if (diff < 3600000) {
            return t('today') + ' ' + date.toLocaleTimeString(currentLang === 'ru' ? 'ru-RU' : 'en-US', { hour: '2-digit', minute: '2-digit' });
        }
        if (diff < 86400000) {
            return t('yesterday') + ' ' + date.toLocaleTimeString(currentLang === 'ru' ? 'ru-RU' : 'en-US', { hour: '2-digit', minute: '2-digit' });
        }
        return formatDate(dateStr);
    }

    // Глобальные функции
    window.editRequest = editRequest;
    window.deleteRequest = deleteRequest;
    window.closeRequestModal = closeRequestModal;
    window.editClient = editClient;
    window.deleteClient = deleteClient;
    window.closeClientModal = closeClientModal;
    window.editEmployee = editEmployee;
    window.deleteEmployee = deleteEmployee;
    window.closeEmployeeModal = closeEmployeeModal;
    window.applySettingsFromPage = applySettingsFromPage;
    </script>
</body>
</html>