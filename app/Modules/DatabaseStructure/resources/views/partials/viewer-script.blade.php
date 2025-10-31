@push('head-scripts')
  @once
    <script defer src="https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
  @endonce
  <script>
    window.databaseStructureViewer = function (
      tables,
      recordsRoute,
      deleteRoute,
      valueRoute,
      recordRoute,
      updateRoute,
      structureRoute,
      manualForeignStoreRoute,
      manualForeignDeleteRoute,
      contentManagementMenuStoreRoute,
      contentManagementMenu,
    ) {
      const extractTables = (payload) => {
          if (Array.isArray(payload)) {
            return payload.filter(Boolean);
          }

          if (payload && typeof payload === 'object') {
            if (Array.isArray(payload.tables)) {
              return payload.tables.filter(Boolean);
            }

            if (Array.isArray(payload.data)) {
              return payload.data.filter(Boolean);
            }

            return Object.values(payload).filter((item) => {
              if (!item || typeof item !== 'object' || Array.isArray(item)) {
                return false;
              }

              const keys = Object.keys(item);

              return keys.some((key) => ['name', 'columns', 'comment', 'engine'].includes(key));
            });
          }

          return [];
        };

        const normalizeNullable = (value) => {
          if (value === true || value === false) {
            return value;
          }

          if (typeof value === 'string') {
            const normalized = value.toLowerCase();
            return ['1', 'true', 'yes', 'y', 't'].includes(normalized);
          }

          if (typeof value === 'number') {
            return value === 1;
          }

          return Boolean(value);
        };

        const normalizeForeignDefinition = (rawForeign) => {
          if (!rawForeign || typeof rawForeign !== 'object' || Array.isArray(rawForeign)) {
            return null;
          }

          const foreignTable = typeof rawForeign.table === 'string' ? rawForeign.table.trim() : '';
          const foreignColumn = typeof rawForeign.column === 'string' ? rawForeign.column.trim() : '';

          if (!foreignTable || !foreignColumn) {
            return null;
          }

          const constraint = typeof rawForeign.constraint === 'string' && rawForeign.constraint.trim() !== ''
            ? rawForeign.constraint.trim()
            : null;

          let displayColumn = null;

          if (typeof rawForeign.display_column === 'string' && rawForeign.display_column.trim() !== '') {
            displayColumn = rawForeign.display_column.trim();
          } else if (typeof rawForeign.displayColumn === 'string' && rawForeign.displayColumn.trim() !== '') {
            displayColumn = rawForeign.displayColumn.trim();
          }

          const rawLabels = Array.isArray(rawForeign.label_columns)
            ? rawForeign.label_columns
            : (Array.isArray(rawForeign.labelColumns) ? rawForeign.labelColumns : []);

          const labelColumns = rawLabels
            .map((label) => (typeof label === 'string' ? label.trim() : ''))
            .filter((label) => label !== '');

          const manual = Boolean(rawForeign.manual);

          return {
            table: foreignTable,
            column: foreignColumn,
            constraint,
            displayColumn,
            labelColumns,
            manual,
          };
        };

        const normalizeColumns = (rawColumns) => {
          if (!Array.isArray(rawColumns)) {
            return [];
          }

          return rawColumns
            .map((column) => {
              if (column && typeof column === 'object' && !Array.isArray(column)) {
                const columnName = typeof column.name === 'string' ? column.name.trim() : '';

                if (!columnName) {
                  return null;
                }

                const normalizedForeign = normalizeForeignDefinition(column.foreign);

                return {
                  name: columnName,
                  type: typeof column.type === 'string' ? column.type.trim() : '',
                  nullable: normalizeNullable(column.nullable),
                  default: Object.prototype.hasOwnProperty.call(column, 'default') ? column.default : null,
                  key: typeof column.key === 'string' && column.key.trim() !== '' ? column.key.trim() : null,
                  extra: typeof column.extra === 'string' && column.extra.trim() !== '' ? column.extra.trim() : null,
                  comment: typeof column.comment === 'string' && column.comment.trim() !== '' ? column.comment.trim() : null,
                  foreign: normalizedForeign,
                };
              }

              if (typeof column === 'string' && column.trim().length > 0) {
                return {
                  name: column.trim(),
                  type: '',
                  nullable: true,
                  default: null,
                  key: null,
                  extra: null,
                  comment: null,
                  foreign: null,
                };
              }

              return null;
            })
            .filter(Boolean);
        };

        const normalizeContentManagementMenu = (menu) => {
          if (!Array.isArray(menu)) {
            return [];
          }

          return menu
            .map((item, index) => {
              if (typeof item === 'string') {
                const table = item.trim();

                if (!table) {
                  return null;
                }

                return {
                  key: `content-${index}-${table}`,
                  table,
                  label: table,
                  description: '',
                };
              }

              if (!item || typeof item !== 'object' || Array.isArray(item)) {
                return null;
              }

              const tableCandidates = [
                typeof item.table === 'string' ? item.table : '',
                typeof item.name === 'string' ? item.name : '',
                typeof item.slug === 'string' ? item.slug : '',
              ];

              const table = tableCandidates
                .map((value) => (typeof value === 'string' ? value.trim() : ''))
                .find((value) => value !== '') || '';

              if (!table) {
                return null;
              }

              const labelSource = [
                typeof item.label === 'string' ? item.label : '',
                typeof item.title === 'string' ? item.title : '',
                table,
              ]
                .map((value) => (typeof value === 'string' ? value.trim() : ''))
                .find((value) => value !== '') || table;

              const description = typeof item.description === 'string' ? item.description.trim() : '';

              const key = typeof item.key === 'string' && item.key.trim() !== ''
                ? item.key.trim()
                : `content-${index}-${table}`;

              return {
                key,
                table,
                label: labelSource,
                description,
              };
            })
            .filter(Boolean);
        };

        const createForeignRecordPreviewState = () => ({
          key: '',
          visible: false,
          loading: false,
          error: null,
          record: null,
          columns: [],
          requestId: 0,
        });

        const createForeignRecordsState = () => ({
          visible: false,
          loading: false,
          loaded: false,
          error: null,
          options: [],
          columns: [],
          page: 1,
          lastPage: 1,
          perPage: 8,
          query: '',
          searchColumn: '',
          requestId: 0,
          selectedValue: '',
          preview: createForeignRecordPreviewState(),
        });

        const normalizedTables = extractTables(tables)
          .map((table) => {
            const tableObject = table && typeof table === 'object' && !Array.isArray(table)
              ? table
              : {};

            const fallbackName = typeof table === 'string' ? table : '';
            const name = typeof tableObject.name === 'string' && tableObject.name.trim().length > 0
              ? tableObject.name
              : fallbackName.trim();

            if (!name) {
              return null;
            }

            const normalizedColumns = normalizeColumns(tableObject.columns);
            const columnNames = normalizedColumns
              .map((column) => (typeof column.name === 'string' ? column.name : ''))
              .filter((column) => column.length > 0);

            let columnsCount = normalizedColumns.length;
            if (Object.prototype.hasOwnProperty.call(tableObject, 'columns_count')) {
              const numeric = Number(tableObject.columns_count);
              if (!Number.isNaN(numeric) && numeric >= 0) {
                columnsCount = Math.max(columnsCount, Math.trunc(numeric));
              }
            }

            const comment = typeof tableObject.comment === 'string' && tableObject.comment !== ''
              ? tableObject.comment
              : null;
            const engine = typeof tableObject.engine === 'string' && tableObject.engine !== ''
              ? tableObject.engine
              : null;

            const structureLoaded = normalizedColumns.length > 0;

            return {
              name,
              comment,
              engine,
              columnsCount,
              structure: {
                loading: false,
                loaded: structureLoaded,
                columns: normalizedColumns,
                error: null,
              },
              open: false,
              structureVisible: true,
              primaryKeys: structureLoaded
                ? normalizedColumns
                  .filter((column) => column && column.key === 'PRI' && column.name)
                  .map((column) => column.name)
                : [],
              records: {
                visible: false,
                loading: false,
                loaded: false,
                rows: [],
                columns: columnNames,
                error: null,
                page: 1,
                perPage: 20,
                total: 0,
                lastPage: 1,
                sort: null,
                direction: 'asc',
                filters: [],
                deletingRowIndex: null,
                search: '',
                searchInput: '',
                searchColumn: '',
                requestId: 0,
              },
            };
          })
          .filter(Boolean);

        const normalizedContentMenu = normalizeContentManagementMenu(contentManagementMenu);

      return {
          activeTab: 'structure',
          query: '',
          recordsRoute,
          recordsDeleteRoute: deleteRoute,
          recordsValueRoute: valueRoute,
          recordsShowRoute: recordRoute,
          recordsUpdateRoute: updateRoute,
          structureRoute,
          manualForeignRoutes: {
            store: typeof manualForeignStoreRoute === 'string' ? manualForeignStoreRoute : '',
            delete: typeof manualForeignDeleteRoute === 'string' ? manualForeignDeleteRoute : '',
          },
          contentManagementMenuRoutes: {
            store: typeof contentManagementMenuStoreRoute === 'string' ? contentManagementMenuStoreRoute : '',
          },
          csrfToken:
            document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ??
            (window.Laravel ? window.Laravel.csrfToken : ''),
          cellPreviewLimit: 120,
          manualForeignModal: {
            open: false,
            sourceTable: '',
            sourceColumn: '',
            targetTable: '',
            targetColumn: '',
            displayColumn: '',
            targetColumns: [],
            saving: false,
            error: null,
          },
          contentManagementMenuModal: {
            open: false,
            table: '',
            label: '',
            description: '',
            saving: false,
            error: null,
          },
          manualForeignErrors: {},
          bodyScrollLocked: false,
          bodyOriginalOverflow: '',
          bodyOriginalPaddingRight: '',
          valueModal: {
            open: false,
            table: '',
            column: '',
            value: '',
            rawValue: null,
            loading: false,
            error: null,
            searchTerm: '',
            editing: false,
            editValue: '',
            saving: false,
            updateError: null,
            identifiers: [],
            foreignKey: null,
            foreignRecords: createForeignRecordsState(),
          },
          filterOperators: [
            { value: '=', label: 'Дорівнює (=)' },
            { value: '!=', label: 'Не дорівнює (!=)' },
            { value: '<', label: 'Менше (<)' },
            { value: '<=', label: 'Менше або дорівнює (<=)' },
            { value: '>', label: 'Більше (>)' },
            { value: '>=', label: 'Більше або дорівнює (>=)' },
            { value: 'like', label: 'Містить (LIKE)' },
            { value: 'not like', label: 'Не містить (NOT LIKE)' },
          ],
          tables: normalizedTables,
          contentManagement: {
            menu: normalizedContentMenu,
            activeKey: normalizedContentMenu[0]?.key ?? '',
            activeTableName: normalizedContentMenu[0]?.table ?? '',
            activeTable: null,
          },
          init() {
            this.syncBodyScrollLock();

            this.$watch('valueModal.open', () => {
              this.syncBodyScrollLock();
            });

            this.$watch('manualForeignModal.open', () => {
              this.syncBodyScrollLock();
            });

            this.$watch('contentManagementMenuModal.open', () => {
              this.syncBodyScrollLock();
            });

            this.$watch('contentManagementMenuModal.table', (value, oldValue) => {
              this.handleContentManagementMenuTableChange(value, oldValue);
            });

            if (this.activeTab === 'content-management') {
              this.ensureContentManagementData();
            }

            this.$watch('activeTab', (value) => {
              if (value === 'content-management') {
                this.ensureContentManagementData();
              }
            });

            this.$watch('contentManagement.activeTableName', (value) => {
              const normalized = typeof value === 'string' ? value.trim() : '';

              if (!normalized) {
                this.contentManagement.activeTable = null;
                return;
              }

              this.contentManagement.activeTable = this.findTableByName(normalized);

              if (!value) {
                return;
              }

              if (this.activeTab === 'content-management') {
                this.ensureContentManagementData();
              }
            });

            const initialTableName = typeof this.contentManagement.activeTableName === 'string'
              ? this.contentManagement.activeTableName.trim()
              : '';

            if (initialTableName) {
              this.contentManagement.activeTable = this.findTableByName(initialTableName);
            }
          },
          get contentManagementActiveItem() {
            if (!this.contentManagement || !Array.isArray(this.contentManagement.menu)) {
              return null;
            }

            const activeKey = typeof this.contentManagement.activeKey === 'string'
              ? this.contentManagement.activeKey.trim()
              : '';

            if (!activeKey) {
              return null;
            }

            return this.contentManagement.menu.find((item) => item && item.key === activeKey) ?? null;
          },
          get contentManagementActiveTable() {
            const storedTable = this.contentManagement?.activeTable;

            if (storedTable && typeof storedTable.name === 'string' && storedTable.name !== '') {
              return storedTable;
            }

            const tableName = typeof this.contentManagement?.activeTableName === 'string'
              ? this.contentManagement.activeTableName.trim()
              : '';

            if (!tableName) {
              return null;
            }

            return this.findTableByName(tableName);
          },
          get filteredTables() {
            if (!this.query) {
              return this.tables;
            }

            const q = this.query.toLowerCase();
            return this.tables.filter((table) => {
              const tableName = typeof table.name === 'string' ? table.name : '';

              if (tableName.toLowerCase().includes(q)) {
                return true;
              }

              const columns = Array.isArray(table.structure?.columns) ? table.structure.columns : [];

              return columns.some((column) => {
                if (!column) {
                  return false;
                }

                const columnName = typeof column.name === 'string' ? column.name : '';
                const columnType = typeof column.type === 'string' ? column.type : '';

                return (
                  columnName.toLowerCase().includes(q) ||
                  columnType.toLowerCase().includes(q)
                );
              });
            });
          },
        syncBodyScrollLock() {
          const shouldLock =
            this.valueModal.open ||
            this.manualForeignModal.open ||
            this.contentManagementMenuModal.open;
          this.toggleBodyScroll(shouldLock);
        },
        toggleBodyScroll(shouldLock) {
          if (typeof document === 'undefined') {
            return;
          }

          const body = document.body;

          if (!body) {
            return;
          }

          if (shouldLock) {
            if (this.bodyScrollLocked) {
              return;
            }

            this.bodyScrollLocked = true;
            this.bodyOriginalOverflow = body.style.overflow || '';
            this.bodyOriginalPaddingRight = body.style.paddingRight || '';

            if (typeof window !== 'undefined' && document.documentElement) {
              const scrollbarWidth = window.innerWidth - document.documentElement.clientWidth;

              if (scrollbarWidth > 0) {
                body.style.paddingRight = `${scrollbarWidth}px`;
              }
            }

            body.style.overflow = 'hidden';

            return;
          }

          if (!this.bodyScrollLocked) {
            return;
          }

          this.bodyScrollLocked = false;
          body.style.overflow = this.bodyOriginalOverflow;
          body.style.paddingRight = this.bodyOriginalPaddingRight;
        },
        handleEscape() {
          if (this.contentManagementMenuModal.open) {
            this.closeContentManagementMenuModal();
            return;
          }

          if (this.valueModal.open) {
            this.closeValueModal();
            return;
          }

          if (this.manualForeignModal.open) {
            this.closeManualForeignModal();
          }
        },
        findTableByName(name) {
          if (typeof name !== 'string') {
            return null;
          }

          const normalized = name.trim();

          if (!normalized) {
            return null;
          }

          return this.tables.find((table) => table && table.name === normalized) ?? null;
        },
        getTableColumnNames(table) {
          if (!table || !table.structure || !Array.isArray(table.structure.columns)) {
            return [];
          }

          return table.structure.columns
            .map((column) => (column && typeof column.name === 'string' ? column.name : null))
            .filter((name) => typeof name === 'string' && name !== '');
        },
        hasManualForeign(tableName, columnName) {
          const table = this.findTableByName(tableName);

          if (!table || !table.structure || !Array.isArray(table.structure.columns)) {
            return false;
          }

          const column = table.structure.columns.find((item) => item && item.name === columnName);

          return Boolean(column && column.foreign && column.foreign.manual);
        },
        async ensureStructureLoadedByName(name) {
          const table = this.findTableByName(name);

          if (!table) {
            return null;
          }

          await this.ensureStructureLoaded(table);

          return table;
        },
        setManualForeignError(tableName, columnName, message) {
          if (!this.manualForeignErrors || typeof this.manualForeignErrors !== 'object') {
            this.manualForeignErrors = {};
          }

          const normalizedTable = typeof tableName === 'string' ? tableName.trim() : '';
          const normalizedColumn = typeof columnName === 'string' ? columnName.trim() : '';

          if (!normalizedTable || !normalizedColumn) {
            return;
          }

          const key = `${normalizedTable}:${normalizedColumn}`;

          if (typeof message === 'string' && message.trim() !== '') {
            this.manualForeignErrors[key] = message.trim();
          } else {
            delete this.manualForeignErrors[key];
          }
        },
        getManualForeignError(tableName, columnName) {
          if (!this.manualForeignErrors) {
            return '';
          }

          const normalizedTable = typeof tableName === 'string' ? tableName.trim() : '';
          const normalizedColumn = typeof columnName === 'string' ? columnName.trim() : '';

          if (!normalizedTable || !normalizedColumn) {
            return '';
          }

          const key = `${normalizedTable}:${normalizedColumn}`;
          const message = this.manualForeignErrors[key];

          return typeof message === 'string' ? message : '';
        },
        getManualForeignRoute(template, tableName, columnName) {
          if (typeof template !== 'string' || template === '') {
            return '';
          }

          const tableValue = typeof tableName === 'string' ? encodeURIComponent(tableName) : '';
          const columnValue = typeof columnName === 'string' ? encodeURIComponent(columnName) : '';

          return template
            .split('__TABLE__').join(tableValue)
            .split('__COLUMN__').join(columnValue);
        },
        updateColumnForeign(table, columnName, foreign) {
          if (!table || !table.structure) {
            return;
          }

          const normalizedColumn = typeof columnName === 'string' ? columnName.trim() : '';

          if (!normalizedColumn) {
            return;
          }

          const columns = Array.isArray(table.structure.columns) ? table.structure.columns : [];
          const column = columns.find((item) => item && item.name === normalizedColumn);

          if (!column) {
            return;
          }

          column.foreign = normalizeForeignDefinition(foreign);
          this.setManualForeignError(table.name, normalizedColumn, '');
        },
        selectContentMenuItem(key) {
          if (!this.contentManagement || !Array.isArray(this.contentManagement.menu)) {
            return;
          }

          const normalizedKey = typeof key === 'string' ? key.trim() : '';

          if (!normalizedKey) {
            this.contentManagement.activeKey = '';
            this.contentManagement.activeTableName = '';
            this.contentManagement.activeTable = null;
            return;
          }

          const item = this.contentManagement.menu.find((entry) => entry && entry.key === normalizedKey);

          if (!item) {
            this.contentManagement.activeKey = '';
            this.contentManagement.activeTableName = '';
            this.contentManagement.activeTable = null;
            return;
          }

          this.contentManagement.activeKey = item.key;
          this.contentManagement.activeTableName = item.table;
          this.contentManagement.activeTable = this.findTableByName(item.table) ?? null;

          if (this.activeTab !== 'content-management') {
            this.activeTab = 'content-management';
          }

          this.ensureContentManagementData();
        },
        handleContentManagementMenuTableChange(value, oldValue) {
          if (!this.contentManagementMenuModal || this.contentManagementMenuModal.saving) {
            return;
          }

          const normalized = typeof value === 'string' ? value.trim() : '';
          const oldNormalized = typeof oldValue === 'string' ? oldValue.trim() : '';

          if (this.contentManagementMenuModal.table !== normalized) {
            this.contentManagementMenuModal.table = normalized;
            return;
          }

          const currentLabel = typeof this.contentManagementMenuModal.label === 'string'
            ? this.contentManagementMenuModal.label.trim()
            : '';

          if (!currentLabel || currentLabel === oldNormalized) {
            this.contentManagementMenuModal.label = normalized;
          }

          if (this.contentManagementMenuModal.error && normalized) {
            this.contentManagementMenuModal.error = null;
          }
        },
        resetContentManagementMenuModal() {
          if (!this.contentManagementMenuModal) {
            return;
          }

          this.contentManagementMenuModal.table = '';
          this.contentManagementMenuModal.label = '';
          this.contentManagementMenuModal.description = '';
          this.contentManagementMenuModal.saving = false;
          this.contentManagementMenuModal.error = null;
        },
        openContentManagementMenuModal() {
          if (!this.contentManagementMenuRoutes.store || !this.contentManagementMenuModal) {
            return;
          }

          if (this.contentManagementMenuModal.saving) {
            return;
          }

          const activeTable = this.contentManagementActiveTable;
          const activeItem = this.contentManagementActiveItem;
          const activeTableName = activeTable && typeof activeTable.name === 'string' ? activeTable.name : '';
          const activeItemTable = activeItem && typeof activeItem.table === 'string' ? activeItem.table : '';
          const defaultTable = activeTableName || activeItemTable || '';

          this.resetContentManagementMenuModal();
          this.contentManagementMenuModal.open = true;
          this.contentManagementMenuModal.table = defaultTable;
          this.contentManagementMenuModal.label = defaultTable;
        },
        closeContentManagementMenuModal() {
          if (!this.contentManagementMenuModal) {
            return;
          }

          if (this.contentManagementMenuModal.saving) {
            return;
          }

          this.contentManagementMenuModal.open = false;
          this.resetContentManagementMenuModal();
        },
        async saveContentManagementMenuItem() {
          if (!this.contentManagementMenuModal || !this.contentManagementMenuRoutes.store) {
            return;
          }

          if (this.contentManagementMenuModal.saving) {
            return;
          }

          const table = typeof this.contentManagementMenuModal.table === 'string'
            ? this.contentManagementMenuModal.table.trim()
            : '';
          const label = typeof this.contentManagementMenuModal.label === 'string'
            ? this.contentManagementMenuModal.label.trim()
            : '';
          const description = typeof this.contentManagementMenuModal.description === 'string'
            ? this.contentManagementMenuModal.description.trim()
            : '';

          if (!table) {
            this.contentManagementMenuModal.error = 'Оберіть таблицю для додавання до меню.';
            return;
          }

          this.contentManagementMenuModal.saving = true;
          this.contentManagementMenuModal.error = null;

          try {
            const response = await fetch(this.contentManagementMenuRoutes.store, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': this.csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
              },
              body: JSON.stringify({
                table,
                label,
                description,
              }),
            });

            const payload = await response.json().catch(() => ({}));

            if (!response.ok) {
              const message = typeof payload === 'object' && payload && typeof payload.message === 'string'
                ? payload.message.trim()
                : '';

              this.contentManagementMenuModal.error = message !== ''
                ? message
                : 'Не вдалося зберегти меню.';
              this.contentManagementMenuModal.saving = false;
              return;
            }

            const itemPayload = payload && typeof payload === 'object' ? (payload.item ?? null) : null;
            const normalizedItems = normalizeContentManagementMenu([itemPayload]);
            const newItem = normalizedItems.length > 0 ? normalizedItems[0] : null;

            if (!newItem) {
              this.contentManagementMenuModal.error = 'Отримано некоректну відповідь сервера.';
              this.contentManagementMenuModal.saving = false;
              return;
            }

            const currentMenu = Array.isArray(this.contentManagement?.menu)
              ? [...this.contentManagement.menu]
              : [];

            currentMenu.push(newItem);
            this.contentManagement.menu = currentMenu;
            this.contentManagement.activeKey = newItem.key;
            this.contentManagement.activeTableName = newItem.table;
            this.contentManagement.activeTable = this.findTableByName(newItem.table) ?? null;

            this.contentManagementMenuModal.open = false;
            this.resetContentManagementMenuModal();

            this.ensureContentManagementData();
          } catch (error) {
            const message = error && typeof error.message === 'string'
              ? error.message
              : 'Сталася помилка під час збереження меню.';
            this.contentManagementMenuModal.error = message;
          } finally {
            if (this.contentManagementMenuModal) {
              this.contentManagementMenuModal.saving = false;
            }
          }
        },
        async openManualForeignModal(table, column) {
          if (!table || !column) {
            return;
          }

          const tableName = typeof table.name === 'string' ? table.name : '';
          const columnName = typeof column.name === 'string' ? column.name : '';

          if (!tableName || !columnName) {
            return;
          }

          await this.ensureStructureLoaded(table);

          this.manualForeignModal.open = true;
          this.manualForeignModal.error = null;
          this.manualForeignModal.saving = false;
          this.manualForeignModal.sourceTable = tableName;
          this.manualForeignModal.sourceColumn = columnName;

          const currentForeign = column.foreign && column.foreign.manual
            ? column.foreign
            : null;

          this.manualForeignModal.targetTable = currentForeign ? currentForeign.table : '';
          this.manualForeignModal.targetColumn = currentForeign ? currentForeign.column : '';
          this.manualForeignModal.displayColumn = currentForeign && currentForeign.displayColumn
            ? currentForeign.displayColumn
            : '';
          this.manualForeignModal.targetColumns = [];

          this.setManualForeignError(tableName, columnName, '');

          if (this.manualForeignModal.targetTable) {
            const targetTable = await this.ensureStructureLoadedByName(this.manualForeignModal.targetTable);
            this.manualForeignModal.targetColumns = this.getTableColumnNames(targetTable);
          }

          if (!Array.isArray(this.manualForeignModal.targetColumns)) {
            this.manualForeignModal.targetColumns = [];
          }

          if (this.manualForeignModal.targetColumn && !this.manualForeignModal.targetColumns.includes(this.manualForeignModal.targetColumn)) {
            this.manualForeignModal.targetColumn = '';
          }

          if (this.manualForeignModal.displayColumn && !this.manualForeignModal.targetColumns.includes(this.manualForeignModal.displayColumn)) {
            this.manualForeignModal.displayColumn = '';
          }
        },
        closeManualForeignModal() {
          this.manualForeignModal.open = false;
          this.manualForeignModal.sourceTable = '';
          this.manualForeignModal.sourceColumn = '';
          this.manualForeignModal.targetTable = '';
          this.manualForeignModal.targetColumn = '';
          this.manualForeignModal.displayColumn = '';
          this.manualForeignModal.targetColumns = [];
          this.manualForeignModal.error = null;
          this.manualForeignModal.saving = false;
        },
        async changeManualForeignTargetTable(value) {
          if (this.manualForeignModal.saving) {
            return;
          }

          const normalized = typeof value === 'string' ? value.trim() : '';
          this.manualForeignModal.targetTable = normalized;
          this.manualForeignModal.targetColumn = '';
          this.manualForeignModal.displayColumn = '';
          this.manualForeignModal.error = null;
          this.manualForeignModal.targetColumns = [];

          if (!normalized) {
            return;
          }

          const targetTable = await this.ensureStructureLoadedByName(normalized);
          this.manualForeignModal.targetColumns = this.getTableColumnNames(targetTable);
        },
        changeManualForeignTargetColumn(value) {
          if (this.manualForeignModal.saving) {
            return;
          }

          const normalized = typeof value === 'string' ? value.trim() : '';
          this.manualForeignModal.targetColumn = normalized;

          if (
            normalized &&
            (!this.manualForeignModal.displayColumn ||
              !this.manualForeignModal.targetColumns.includes(this.manualForeignModal.displayColumn))
          ) {
            this.manualForeignModal.displayColumn = normalized;
          }
        },
        changeManualForeignDisplayColumn(value) {
          if (this.manualForeignModal.saving) {
            return;
          }

          this.manualForeignModal.displayColumn = typeof value === 'string' ? value.trim() : '';
        },
        async saveManualForeign() {
          if (this.manualForeignModal.saving) {
            return;
          }

          const tableName = this.manualForeignModal.sourceTable;
          const columnName = this.manualForeignModal.sourceColumn;

          if (!tableName || !columnName) {
            this.manualForeignModal.error = 'Не вдалося визначити колонку для збереження зв\'язку.';
            return;
          }

          const routeTemplate = this.manualForeignRoutes.store;

          if (!routeTemplate) {
            this.manualForeignModal.error = 'Маршрут для збереження ручного зв\'язку не налаштовано.';
            return;
          }

          const targetTable = this.manualForeignModal.targetTable;
          const targetColumn = this.manualForeignModal.targetColumn;

          if (!targetTable || !targetColumn) {
            this.manualForeignModal.error = 'Оберіть таблицю та колонку для зв\'язку.';
            return;
          }

          const displayColumn = this.manualForeignModal.displayColumn;
          const route = this.getManualForeignRoute(routeTemplate, tableName, columnName);

          if (!route) {
            this.manualForeignModal.error = 'Маршрут для збереження ручного зв\'язку не налаштовано.';
            return;
          }

          const payload = {
            foreign_table: targetTable,
            foreign_column: targetColumn,
          };

          if (displayColumn) {
            payload.display_column = displayColumn;
          }

          this.manualForeignModal.saving = true;
          this.manualForeignModal.error = null;

          try {
            const response = await fetch(new URL(route, window.location.origin).toString(), {
              method: 'POST',
              headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken || '',
              },
              body: JSON.stringify(payload),
            });

            const data = await response.json().catch(() => ({}));

            if (!response.ok) {
              const message = data?.message || 'Не вдалося зберегти ручний зв\'язок.';
              throw new Error(message);
            }

            const sourceTable = this.findTableByName(tableName);

            if (sourceTable) {
              this.updateColumnForeign(sourceTable, columnName, data?.foreign ?? null);
            }

            this.manualForeignModal.saving = false;
            this.closeManualForeignModal();
          } catch (error) {
            this.manualForeignModal.saving = false;
            this.manualForeignModal.error = error?.message ?? 'Сталася помилка під час збереження ручного зв\'язку.';
          }
        },
        async deleteManualForeignFromModal() {
          if (this.manualForeignModal.saving) {
            return;
          }

          const tableName = this.manualForeignModal.sourceTable;
          const columnName = this.manualForeignModal.sourceColumn;

          if (!tableName || !columnName) {
            this.manualForeignModal.error = 'Не вдалося визначити колонку для видалення.';
            return;
          }

          const table = this.findTableByName(tableName);

          if (!table) {
            this.manualForeignModal.error = 'Не вдалося знайти таблицю для видалення зв\'язку.';
            return;
          }

          const columns = Array.isArray(table.structure?.columns) ? table.structure.columns : [];
          const column = columns.find((item) => item && item.name === columnName);

          if (!column) {
            this.manualForeignModal.error = 'Не вдалося знайти колонку для видалення зв\'язку.';
            return;
          }

          this.manualForeignModal.saving = true;
          this.manualForeignModal.error = null;

          const success = await this.removeManualForeign(table, column);

          this.manualForeignModal.saving = false;

          if (success) {
            this.closeManualForeignModal();
          } else {
            const errorMessage = this.getManualForeignError(tableName, columnName);
            this.manualForeignModal.error = errorMessage || 'Не вдалося видалити ручний зв\'язок.';
          }
        },
        async removeManualForeign(table, column) {
          if (!table || !column) {
            return false;
          }

          const tableName = typeof table.name === 'string' ? table.name : '';
          const columnName = typeof column.name === 'string' ? column.name : '';

          if (!tableName || !columnName) {
            return false;
          }

          const routeTemplate = this.manualForeignRoutes.delete;

          if (!routeTemplate) {
            this.setManualForeignError(tableName, columnName, 'Маршрут для видалення ручного зв\'язку не налаштовано.');
            return false;
          }

          const route = this.getManualForeignRoute(routeTemplate, tableName, columnName);

          if (!route) {
            this.setManualForeignError(tableName, columnName, 'Маршрут для видалення ручного зв\'язку не налаштовано.');
            return false;
          }

          try {
            const response = await fetch(new URL(route, window.location.origin).toString(), {
              method: 'DELETE',
              headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken || '',
              },
            });

            const data = await response.json().catch(() => ({}));

            if (!response.ok) {
              const message = data?.message || 'Не вдалося видалити ручний зв\'язок.';
              throw new Error(message);
            }

            this.updateColumnForeign(table, columnName, data?.foreign ?? null);

            return true;
          } catch (error) {
            const message = error?.message ?? 'Сталася помилка під час видалення ручного зв\'язку.';
            this.setManualForeignError(tableName, columnName, message);
            return false;
          }
        },
        async confirmManualForeignRemoval(table, column) {
          if (!table || !column) {
            return;
          }

          if (!window.confirm('Видалити ручний зв\'язок для цього поля?')) {
            return;
          }

          await this.removeManualForeign(table, column);
        },
        async toggleTable(table) {
          if (!table) {
            return;
          }

          table.open = !table.open;

          if (table.open) {
            await this.ensureStructureLoaded(table);
          }
        },
        async ensureContentManagementData() {
          if (this.activeTab !== 'content-management') {
            return;
          }

          const table = this.contentManagementActiveTable;

          if (!table || !table.records) {
            return;
          }

          table.records.visible = true;

          await this.ensureStructureLoaded(table);

          if (!table.records.loaded && !table.records.loading) {
            await this.loadRecords(table);
          }
        },
        async ensureStructureLoaded(table) {
          if (!table || !table.structure) {
            return;
          }

          if (table.structure.loading || table.structure.loaded) {
            return;
          }

          if (table.structure.error) {
            table.structure.error = null;
          }

          if (Array.isArray(table.structure.columns) && table.structure.columns.length > 0) {
            table.structure.loaded = true;
            return;
          }

          if (!this.structureRoute) {
            table.structure.loaded = true;
            return;
          }

          await this.loadTableStructure(table);
        },
        async loadTableStructure(table) {
          if (!table || !this.structureRoute) {
            return;
          }

          table.structure.loading = true;
          table.structure.error = null;

          try {
            const url = new URL(
              this.structureRoute.replace('__TABLE__', encodeURIComponent(table.name)),
              window.location.origin
            );

            const response = await fetch(url.toString(), {
              headers: {
                Accept: 'application/json',
              },
            });

            if (!response.ok) {
              const payload = await response.json().catch(() => null);
              const message = payload?.message || 'Не вдалося завантажити структуру таблиці.';
              throw new Error(message);
            }

            const data = await response.json();
            this.setTableColumns(table, data.columns);

            if (Object.prototype.hasOwnProperty.call(data, 'columns_count')) {
              const numericCount = Number(data.columns_count);
              if (!Number.isNaN(numericCount) && numericCount >= 0) {
                table.columnsCount = Math.max(table.columnsCount, Math.trunc(numericCount));
              }
            }
          } catch (error) {
            table.structure.error = error.message ?? 'Сталася помилка під час завантаження структури таблиці.';
            table.structure.loaded = false;
          } finally {
            table.structure.loading = false;
          }
        },
        setTableColumns(table, columns) {
          if (!table || !table.structure) {
            return;
          }

          const normalizedColumns = normalizeColumns(columns);
          table.structure.columns = normalizedColumns;
          table.structure.loaded = true;
          const currentCount = typeof table.columnsCount === 'number' && !Number.isNaN(table.columnsCount)
            ? table.columnsCount
            : 0;
          table.columnsCount = Math.max(currentCount, normalizedColumns.length);
          table.primaryKeys = normalizedColumns
            .filter((column) => column && column.key === 'PRI' && column.name)
            .map((column) => column.name);

          if (!Array.isArray(table.records.columns) || table.records.columns.length === 0) {
            table.records.columns = normalizedColumns.map((column) => column.name).filter(Boolean);
          }
        },
        async toggleRecords(table) {
          table.records.visible = !table.records.visible;
          table.records.error = null;

          if (table.records.visible && !table.records.loaded) {
            await this.loadRecords(table);
          }
        },
        async loadRecords(table) {
          const requestId = (table.records.requestId ?? 0) + 1;
          table.records.requestId = requestId;
          table.records.loading = true;
          table.records.error = null;
          table.records.loaded = false;

          try {
            const previousFilters = table.records.filters.map((filter) => ({ ...filter }));
            const url = new URL(
              this.recordsRoute.replace('__TABLE__', encodeURIComponent(table.name)),
              window.location.origin
            );

            url.searchParams.set('page', table.records.page);
            url.searchParams.set('per_page', table.records.perPage);

            if (table.records.sort) {
              url.searchParams.set('sort', table.records.sort);
              url.searchParams.set('direction', table.records.direction);
            }

            const searchQuery = typeof table.records.search === 'string' ? table.records.search.trim() : '';
            const searchColumn = typeof table.records.searchColumn === 'string'
              ? table.records.searchColumn.trim()
              : '';

            if (searchQuery) {
              url.searchParams.set('search', searchQuery);
            }

            if (searchColumn) {
              url.searchParams.set('search_column', searchColumn);
            }

            table.records.filters.forEach((filter, index) => {
              if (!filter || !filter.column || !filter.operator) {
                return;
              }

              url.searchParams.append(`filters[${index}][column]`, filter.column);
              url.searchParams.append(`filters[${index}][operator]`, filter.operator);

              if (this.operatorRequiresValue(filter.operator)) {
                const value = filter.value ?? '';
                url.searchParams.append(`filters[${index}][value]`, value === null ? '' : String(value));
              }
            });

            const response = await fetch(url.toString(), {
              headers: {
                Accept: 'application/json',
              },
            });

            if (!response.ok) {
              throw new Error('Не вдалося завантажити записи.');
            }

            const data = await response.json();

            if (table.records.requestId !== requestId) {
              return;
            }

            table.records.rows = data.rows || [];
            table.records.columns = Array.isArray(data.columns)
              ? data.columns.filter((name) => typeof name === 'string')
              : table.records.columns;
            table.records.page = data.page || 1;
            table.records.perPage = data.per_page || table.records.perPage;
            table.records.total = data.total ?? table.records.total;
            table.records.lastPage = data.last_page || 1;
            table.records.sort = data.sort || null;
            table.records.direction = data.direction || table.records.direction;
            const filtersFromResponse = Array.isArray(data.filters) ? data.filters : previousFilters;
            table.records.filters = this.normalizeFilters(filtersFromResponse, previousFilters);

            if (typeof data.search === 'string') {
              table.records.search = data.search;
              table.records.searchInput = data.search;
            }

            const responseSearchColumn = typeof data.search_column === 'string'
              ? data.search_column
              : table.records.searchColumn;
            table.records.searchColumn = responseSearchColumn || '';

            if (
              table.records.searchColumn &&
              Array.isArray(table.records.columns) &&
              !table.records.columns.includes(table.records.searchColumn)
            ) {
              table.records.searchColumn = '';
            }

            table.records.loaded = true;
          } catch (error) {
            if (table.records.requestId !== requestId) {
              return;
            }

            table.records.error = error.message ?? 'Сталася помилка під час завантаження записів.';
          } finally {
            if (table.records.requestId === requestId) {
              table.records.loading = false;
            }
          }
        },
        changePage(table, page) {
          if (table.records.loading) {
            return;
          }

          const target = Math.min(Math.max(page, 1), table.records.lastPage || 1);

          if (target === table.records.page) {
            return;
          }

          table.records.page = target;
          this.loadRecords(table);
        },
        changePerPage(table, perPage) {
          if (table.records.loading) {
            return;
          }

          table.records.perPage = Number(perPage) || table.records.perPage;
          table.records.page = 1;
          this.loadRecords(table);
        },
        async deleteRecord(table, row, rowIndex) {
          if (table.records.loading) {
            return;
          }

          if (!row || typeof row !== 'object') {
            return;
          }

          if (!window.confirm('Ви впевнені, що хочете видалити цей запис?')) {
            return;
          }

          const identifiers = this.buildIdentifiers(table, row);

          if (identifiers.length === 0) {
            table.records.error = 'Не вдалося визначити ідентифікатори запису для видалення.';
            return;
          }

          table.records.error = null;
          table.records.deletingRowIndex = rowIndex;

          try {
            const url = new URL(
              this.recordsDeleteRoute.replace('__TABLE__', encodeURIComponent(table.name)),
              window.location.origin
            );

            const response = await fetch(url.toString(), {
              method: 'DELETE',
              headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken || '',
              },
              body: JSON.stringify({ identifiers }),
            });

            if (!response.ok) {
              const payload = await response.json().catch(() => null);
              const message = payload?.message || 'Не вдалося видалити запис.';
              throw new Error(message);
            }

            await this.loadRecords(table);
          } catch (error) {
            table.records.error = error.message ?? 'Сталася помилка під час видалення запису.';
          } finally {
            table.records.deletingRowIndex = null;
          }
        },
        buildIdentifiers(table, row) {
          const identifiers = [];
          const primaryKeys = Array.isArray(table.primaryKeys)
            ? table.primaryKeys.filter(Boolean)
            : [];
          const fallbackColumns = Array.isArray(table.structure?.columns)
            ? table.structure.columns.map((column) => column.name)
            : [];
          const columns = primaryKeys.length > 0
            ? primaryKeys
            : (Array.isArray(table.records.columns) && table.records.columns.length > 0
              ? table.records.columns
              : fallbackColumns);

          columns.forEach((column) => {
            if (!column || typeof column !== 'string') {
              return;
            }

            if (!Object.prototype.hasOwnProperty.call(row, column)) {
              return;
            }

            const value = row[column];

            if (value === undefined) {
              return;
            }

            if (value !== null && typeof value === 'object') {
              return;
            }

            identifiers.push({
              column,
              value,
            });
          });

          return identifiers;
        },
        findForeignKey(table, columnName) {
          if (!table || typeof columnName !== 'string' || !table.structure) {
            return null;
          }

          const normalizedColumn = columnName.trim();

          if (!normalizedColumn) {
            return null;
          }

          const columns = Array.isArray(table.structure.columns)
            ? table.structure.columns
            : [];

          const column = columns.find((item) => item && item.name === normalizedColumn);

          if (!column || !column.foreign) {
            return null;
          }

          const foreign = column.foreign;
          const foreignTable = typeof foreign.table === 'string' ? foreign.table : '';
          const foreignColumn = typeof foreign.column === 'string' ? foreign.column : '';

          if (!foreignTable || !foreignColumn) {
            return null;
          }

          const constraint = typeof foreign.constraint === 'string' && foreign.constraint !== ''
            ? foreign.constraint
            : null;
          const displayColumn = typeof foreign.displayColumn === 'string' && foreign.displayColumn !== ''
            ? foreign.displayColumn
            : null;
          const labelColumns = Array.isArray(foreign.labelColumns)
            ? foreign.labelColumns.filter((label) => typeof label === 'string' && label !== '')
            : [];

          return {
            table: foreignTable,
            column: foreignColumn,
            constraint,
            displayColumn,
            labelColumns,
          };
        },
        toggleForeignRecords() {
          if (!this.valueModal.foreignKey) {
            return;
          }

          const nextVisible = !this.valueModal.foreignRecords.visible;
          this.valueModal.foreignRecords.visible = nextVisible;

          if (nextVisible) {
            if (!this.valueModal.foreignRecords.loaded) {
              this.loadForeignRecords();
            }

            this.$nextTick(() => {
              this.scrollValueModalToTop();
            });
          } else {
            this.resetForeignRecordPreview();
            this.$nextTick(() => {
              this.autoResizeValueEditor();
            });
          }
        },
        resetForeignRecordPreview() {
          if (!this.valueModal.foreignRecords) {
            return;
          }

          this.valueModal.foreignRecords.preview = createForeignRecordPreviewState();
        },
        async loadForeignRecords(page = null) {
          const foreignKey = this.valueModal.foreignKey;

          if (!foreignKey) {
            return;
          }

          if (!this.recordsRoute) {
            this.valueModal.foreignRecords.error = 'Маршрут для завантаження записів не налаштовано.';
            this.valueModal.foreignRecords.loaded = false;
            return;
          }

          if (typeof page === 'number' && Number.isFinite(page)) {
            this.valueModal.foreignRecords.page = Math.max(1, Math.trunc(page));
          }

          const currentPage = this.valueModal.foreignRecords.page || 1;
          const perPage = this.valueModal.foreignRecords.perPage || 8;
          const requestId = (this.valueModal.foreignRecords.requestId ?? 0) + 1;

          this.valueModal.foreignRecords.requestId = requestId;
          this.valueModal.foreignRecords.loading = true;
          this.valueModal.foreignRecords.error = null;
          this.valueModal.foreignRecords.preview = createForeignRecordPreviewState();

          try {
            const url = new URL(
              this.recordsRoute.replace('__TABLE__', encodeURIComponent(foreignKey.table)),
              window.location.origin,
            );

            url.searchParams.set('page', currentPage);
            url.searchParams.set('per_page', perPage);

            const query = typeof this.valueModal.foreignRecords.query === 'string'
              ? this.valueModal.foreignRecords.query.trim()
              : '';
            const searchColumn = typeof this.valueModal.foreignRecords.searchColumn === 'string'
              ? this.valueModal.foreignRecords.searchColumn.trim()
              : '';

            if (query) {
              url.searchParams.set('search', query);
            }

            if (searchColumn) {
              url.searchParams.set('search_column', searchColumn);
            }

            const response = await fetch(url.toString(), {
              headers: {
                Accept: 'application/json',
              },
            });

            if (!response.ok) {
              const payload = await response.json().catch(() => null);
              const message = payload?.message || 'Не вдалося завантажити пов\'язані записи.';
              throw new Error(message);
            }

            const data = await response.json();

            if (this.valueModal.foreignRecords.requestId !== requestId) {
              return;
            }

            this.valueModal.foreignRecords.options = Array.isArray(data.rows) ? data.rows : [];
            this.valueModal.foreignRecords.columns = Array.isArray(data.columns)
              ? data.columns.filter((name) => typeof name === 'string' && name !== '')
              : [];
            const responseSearchColumn = typeof data.search_column === 'string'
              ? data.search_column
              : this.valueModal.foreignRecords.searchColumn;
            this.valueModal.foreignRecords.searchColumn = responseSearchColumn || '';

            if (
              this.valueModal.foreignRecords.searchColumn &&
              !this.valueModal.foreignRecords.columns.includes(this.valueModal.foreignRecords.searchColumn)
            ) {
              this.valueModal.foreignRecords.searchColumn = '';
            }
            this.valueModal.foreignRecords.page = data.page || currentPage;
            this.valueModal.foreignRecords.lastPage = data.last_page || 1;
            this.valueModal.foreignRecords.loaded = true;
          } catch (error) {
            if (this.valueModal.foreignRecords.requestId !== requestId) {
              return;
            }

            this.valueModal.foreignRecords.error = error.message ?? 'Сталася помилка під час завантаження пов’язаних записів.';
            this.valueModal.foreignRecords.loaded = false;
          } finally {
            if (this.valueModal.foreignRecords.requestId === requestId) {
              this.valueModal.foreignRecords.loading = false;
            }
          }
        },
        searchForeignRecords() {
          if (!this.valueModal.foreignKey) {
            return;
          }

          this.valueModal.foreignRecords.page = 1;
          this.loadForeignRecords(1);
        },
        updateForeignRecordsSearchColumn(column) {
          if (!this.valueModal.foreignKey) {
            return;
          }

          const normalized = typeof column === 'string' ? column.trim() : '';
          const previous = typeof this.valueModal.foreignRecords.searchColumn === 'string'
            ? this.valueModal.foreignRecords.searchColumn.trim()
            : '';

          if (normalized === previous && this.valueModal.foreignRecords.loaded) {
            this.valueModal.foreignRecords.searchColumn = normalized;
            return;
          }

          this.valueModal.foreignRecords.searchColumn = normalized;
          this.valueModal.foreignRecords.page = 1;
          this.loadForeignRecords(1);
        },
        changeForeignRecordsPage(page) {
          if (this.valueModal.foreignRecords.loading) {
            return;
          }

          const numericPage = Number(page);

          if (!Number.isFinite(numericPage)) {
            return;
          }

          const lastPage = this.valueModal.foreignRecords.lastPage || 1;
          const target = Math.min(Math.max(Math.trunc(numericPage), 1), lastPage);

          if (target === (this.valueModal.foreignRecords.page || 1)) {
            return;
          }

          this.valueModal.foreignRecords.page = target;
          this.loadForeignRecords(target);
        },
        selectForeignRecord(record) {
          const foreignKey = this.valueModal.foreignKey;

          if (!foreignKey || !record || typeof record !== 'object') {
            return;
          }

          const value = record[foreignKey.column];

          if (value === undefined) {
            return;
          }

          this.valueModal.editValue = value === null ? '' : String(value);
          this.valueModal.foreignRecords.selectedValue = this.normalizeForeignSelectionValue(value);
          this.resetForeignRecordPreview();
          this.$nextTick(() => {
            this.autoResizeValueEditor();
          });
        },
        foreignRecordKey(record) {
          const foreignKey = this.valueModal.foreignKey;

          if (foreignKey && record && typeof record === 'object') {
            const value = record[foreignKey.column];

            if (value !== undefined && value !== null) {
              return `${foreignKey.table}:${foreignKey.column}:${String(value)}`;
            }
          }

          try {
            return JSON.stringify(record ?? {});
          } catch (error) {
            return String(Math.random());
          }
        },
        isForeignRecordSelected(record) {
          const foreignKey = this.valueModal.foreignKey;

          if (!foreignKey || !record || typeof record !== 'object') {
            return false;
          }

          const value = record[foreignKey.column];
          const normalized = this.normalizeForeignSelectionValue(value);

          if (normalized === '') {
            return false;
          }

          return normalized === this.valueModal.foreignRecords.selectedValue;
        },
        isForeignRecordPreviewed(record) {
          const preview = this.valueModal.foreignRecords?.preview;

          if (!preview) {
            return false;
          }

          const key = this.foreignRecordKey(record);

          return (
            preview.visible &&
            typeof preview.key === 'string' &&
            preview.key !== '' &&
            preview.key === key
          );
        },
        buildForeignRecordIdentifiers(record) {
          const foreignKey = this.valueModal.foreignKey;

          if (!foreignKey || !record || typeof record !== 'object') {
            return [];
          }

          const value = record[foreignKey.column];

          if (value !== null && typeof value === 'object') {
            return [];
          }

          if (value === undefined) {
            return [];
          }

          return [
            {
              column: foreignKey.column,
              value,
            },
          ];
        },
        async previewForeignRecord(record) {
          const foreignKey = this.valueModal.foreignKey;

          if (!foreignKey || !record || typeof record !== 'object') {
            return;
          }

          const key = this.foreignRecordKey(record);

          if (!key) {
            return;
          }

          const preview = this.valueModal.foreignRecords.preview;

          if (preview.visible && preview.key === key) {
            this.resetForeignRecordPreview();
            return;
          }

          const identifiers = this.buildForeignRecordIdentifiers(record);

          if (identifiers.length === 0) {
            this.valueModal.foreignRecords.preview = {
              ...createForeignRecordPreviewState(),
              visible: true,
              key,
              error: 'Не вдалося визначити ідентифікатори пов’язаного запису.',
            };
            return;
          }

          if (!this.recordsShowRoute) {
            this.valueModal.foreignRecords.preview = {
              ...createForeignRecordPreviewState(),
              visible: true,
              key,
              error: 'Маршрут для завантаження повного запису не налаштовано.',
            };
            return;
          }

          const requestId = (preview.requestId ?? 0) + 1;

          this.valueModal.foreignRecords.preview = {
            ...createForeignRecordPreviewState(),
            visible: true,
            key,
            loading: true,
            requestId,
          };

          try {
            const url = new URL(
              this.recordsShowRoute.replace('__TABLE__', encodeURIComponent(foreignKey.table)),
              window.location.origin,
            );

            const response = await fetch(url.toString(), {
              method: 'POST',
              headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken || '',
              },
              body: JSON.stringify({
                identifiers,
              }),
            });

            if (!response.ok) {
              const payload = await response.json().catch(() => null);
              const message = payload?.message || 'Не вдалося завантажити повний запис.';
              throw new Error(message);
            }

            const data = await response.json();

            if (this.valueModal.foreignRecords.preview.requestId !== requestId) {
              return;
            }

            const recordData = data && typeof data === 'object' && !Array.isArray(data.record)
              ? (data.record ?? {})
              : {};
            const normalizedRecord = recordData && typeof recordData === 'object' ? recordData : {};
            const columns = Array.isArray(data?.columns)
              ? data.columns.filter((column) => typeof column === 'string' && column !== '')
              : Object.keys(normalizedRecord);

            this.valueModal.foreignRecords.preview.loading = false;
            this.valueModal.foreignRecords.preview.error = null;
            this.valueModal.foreignRecords.preview.record = normalizedRecord;
            this.valueModal.foreignRecords.preview.columns = columns;
          } catch (error) {
            if (this.valueModal.foreignRecords.preview.requestId !== requestId) {
              return;
            }

            this.valueModal.foreignRecords.preview.loading = false;
            this.valueModal.foreignRecords.preview.error = error.message ?? 'Сталася помилка під час завантаження запису.';
          } finally {
            if (this.valueModal.foreignRecords.preview.requestId === requestId) {
              this.valueModal.foreignRecords.preview.loading = false;
            }

            this.$nextTick(() => {
              this.scrollValueModalToTop();
            });
          }
        },
        formatForeignRecordLabel(record) {
          const foreignKey = this.valueModal.foreignKey;

          if (!foreignKey || !record || typeof record !== 'object') {
            return '';
          }

          const labelColumns = Array.isArray(foreignKey.labelColumns) && foreignKey.labelColumns.length > 0
            ? foreignKey.labelColumns
            : [];

          const candidates = labelColumns.length > 0
            ? labelColumns
            : [foreignKey.displayColumn, foreignKey.column].filter((column) => typeof column === 'string' && column);

          for (const column of candidates) {
            if (!column || !Object.prototype.hasOwnProperty.call(record, column)) {
              continue;
            }

            const value = record[column];
            const formatted = this.formatCell(value);

            if (formatted && formatted !== '—') {
              return formatted;
            }
          }

          return this.formatCell(record[foreignKey.column]);
        },
        formatForeignRecordSummary(record) {
          const foreignKey = this.valueModal.foreignKey;

          if (!foreignKey || !record || typeof record !== 'object') {
            return '';
          }

          const columns = Array.isArray(this.valueModal.foreignRecords.columns)
            ? this.valueModal.foreignRecords.columns.filter((name) => typeof name === 'string' && name !== '')
            : Object.keys(record);

          const summary = [];

          columns.forEach((column) => {
            if (column === foreignKey.column) {
              return;
            }

            if (!Object.prototype.hasOwnProperty.call(record, column)) {
              return;
            }

            const value = record[column];
            const formatted = this.formatCell(value);

            if (!formatted || formatted === '—') {
              return;
            }

            summary.push(`${column}: ${formatted}`);
          });

          return summary.slice(0, 3).join(' • ');
        },
        normalizeForeignSelectionValue(value) {
          if (value === null || value === undefined) {
            return '';
          }

          if (typeof value === 'object') {
            try {
              return JSON.stringify(value);
            } catch (error) {
              return '';
            }
          }

          return String(value);
        },
        syncForeignSelectionWithEditValue() {
          if (!this.valueModal.foreignKey) {
            this.valueModal.foreignRecords.selectedValue = '';
            return;
          }

          this.valueModal.foreignRecords.selectedValue = this.normalizeForeignSelectionValue(
            this.valueModal.editValue,
          );
        },
        syncForeignSelectionWithRawValue(value) {
          if (!this.valueModal.foreignKey) {
            this.valueModal.foreignRecords.selectedValue = '';
            return;
          }

          this.valueModal.foreignRecords.selectedValue = this.normalizeForeignSelectionValue(value);
        },
        scrollValueModalToTop() {
          const overlay = this.$refs?.valueModalOverlay;

          if (!overlay) {
            return;
          }

          overlay.scrollTop = 0;
        },
        autoResizeValueEditor(element = null) {
          const textarea = element || this.$refs?.valueEditor;

          if (!textarea) {
            return;
          }

          requestAnimationFrame(() => {
            textarea.style.height = 'auto';
            textarea.style.height = `${textarea.scrollHeight}px`;

            if (this.valueModal.editing) {
              this.scrollValueModalToTop();
            }
          });
        },
        async showRecordValue(table, column, row) {
          const columnName = typeof column === 'string' ? column.trim() : '';
          const tableName = table && typeof table.name === 'string' ? table.name : '';

          if (!columnName || !tableName) {
            return;
          }

          const identifiers = this.buildIdentifiers(table, row);
          const clonedIdentifiers = identifiers
            .filter((identifier) => identifier && typeof identifier.column === 'string' && identifier.column !== '')
            .map((identifier) => ({
              column: identifier.column,
              value: identifier.value,
            }));

          this.valueModal.open = true;
          this.valueModal.loading = true;
          this.valueModal.error = null;
          this.valueModal.updateError = null;
          this.valueModal.table = tableName;
          this.valueModal.column = columnName;
          this.valueModal.value = '';
          this.valueModal.rawValue = null;
          this.valueModal.editValue = '';
          this.valueModal.editing = false;
          this.valueModal.saving = false;
          this.valueModal.identifiers = clonedIdentifiers;
          this.valueModal.foreignKey = null;
          this.valueModal.foreignRecords = createForeignRecordsState();

          const records = table && typeof table === 'object' ? table.records || {} : {};
          const searchTerm = typeof records.search === 'string' ? records.search : '';
          const searchColumn = typeof records.searchColumn === 'string' ? records.searchColumn.trim() : '';
          this.valueModal.searchTerm = !searchTerm || (searchColumn && searchColumn !== columnName)
            ? ''
            : searchTerm;

          if (clonedIdentifiers.length === 0) {
            this.valueModal.loading = false;
            this.valueModal.error = 'Не вдалося визначити ідентифікатори запису для отримання значення.';
            return;
          }

          if (!this.recordsValueRoute) {
            this.valueModal.loading = false;
            this.valueModal.error = 'Маршрут для отримання значення не налаштовано.';
            return;
          }

          await this.ensureStructureLoaded(table);
          this.valueModal.foreignKey = this.findForeignKey(table, columnName);
          this.valueModal.foreignRecords = createForeignRecordsState();

          try {
            const url = new URL(
              this.recordsValueRoute.replace('__TABLE__', encodeURIComponent(tableName)),
              window.location.origin
            );

            const response = await fetch(url.toString(), {
              method: 'POST',
              headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken || '',
              },
              body: JSON.stringify({
                column: columnName,
                identifiers: clonedIdentifiers,
              }),
            });

            if (!response.ok) {
              const payload = await response.json().catch(() => null);
              const message = payload?.message || 'Не вдалося отримати повне значення.';
              throw new Error(message);
            }

            const payload = await response.json();
            const rawValue = payload && Object.prototype.hasOwnProperty.call(payload, 'value')
              ? payload.value
              : null;

            this.valueModal.rawValue = rawValue;
            this.valueModal.value = this.formatCell(rawValue);
            this.valueModal.editValue = this.prepareEditableValue(rawValue);
            this.syncForeignSelectionWithRawValue(rawValue);
          } catch (error) {
            this.valueModal.error = error.message ?? 'Сталася помилка під час отримання значення.';
          } finally {
            this.valueModal.loading = false;
          }
        },
        startEditingValue() {
          if (this.valueModal.loading || this.valueModal.error) {
            return;
          }

          this.valueModal.editValue = this.prepareEditableValue(this.valueModal.rawValue);
          this.valueModal.editing = true;
          this.valueModal.updateError = null;
          this.syncForeignSelectionWithEditValue();
          this.$nextTick(() => {
            this.scrollValueModalToTop();
            this.autoResizeValueEditor();
          });
        },
        cancelEditingValue() {
          if (this.valueModal.saving) {
            return;
          }

          this.valueModal.editValue = this.prepareEditableValue(this.valueModal.rawValue);
          this.valueModal.editing = false;
          this.valueModal.updateError = null;
          this.valueModal.foreignRecords.visible = false;
          this.syncForeignSelectionWithRawValue(this.valueModal.rawValue);
        },
        async saveEditedValue() {
          if (this.valueModal.saving || this.valueModal.loading) {
            return;
          }

          const tableName = typeof this.valueModal.table === 'string' ? this.valueModal.table : '';
          const columnName = typeof this.valueModal.column === 'string' ? this.valueModal.column : '';
          const identifiers = Array.isArray(this.valueModal.identifiers)
            ? this.valueModal.identifiers
              .filter((identifier) => identifier && typeof identifier.column === 'string' && identifier.column !== '')
              .map((identifier) => ({
                column: identifier.column,
                value: identifier.value,
              }))
            : [];

          if (!tableName || !columnName) {
            this.valueModal.updateError = 'Невідомо, яке значення оновлювати.';
            return;
          }

          if (identifiers.length === 0) {
            this.valueModal.updateError = 'Не вдалося визначити ідентифікатори запису для збереження.';
            return;
          }

          if (!this.recordsUpdateRoute) {
            this.valueModal.updateError = 'Маршрут для збереження значення не налаштовано.';
            return;
          }

          this.valueModal.saving = true;
          this.valueModal.updateError = null;

          try {
            const url = new URL(
              this.recordsUpdateRoute.replace('__TABLE__', encodeURIComponent(tableName)),
              window.location.origin
            );

            const response = await fetch(url.toString(), {
              method: 'PUT',
              headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken || '',
              },
              body: JSON.stringify({
                column: columnName,
                value: this.valueModal.editValue,
                identifiers,
              }),
            });

            if (!response.ok) {
              const payload = await response.json().catch(() => null);
              const message = payload?.message || 'Не вдалося зберегти значення.';
              throw new Error(message);
            }

            const payload = await response.json();
            const updatedValue = payload && Object.prototype.hasOwnProperty.call(payload, 'value')
              ? payload.value
              : null;

            this.valueModal.rawValue = updatedValue;
            this.valueModal.value = this.formatCell(updatedValue);
            this.valueModal.editValue = this.prepareEditableValue(updatedValue);
            this.valueModal.editing = false;
            this.valueModal.updateError = null;
            this.syncForeignSelectionWithRawValue(updatedValue);

            this.valueModal.identifiers = identifiers.map((identifier) => {
              if (identifier.column === columnName) {
                return {
                  column: identifier.column,
                  value: updatedValue,
                };
              }

              return identifier;
            });
          } catch (error) {
            this.valueModal.updateError = error.message ?? 'Сталася помилка під час збереження значення.';
          } finally {
            this.valueModal.saving = false;
          }
        },
        closeValueModal() {
          this.valueModal.open = false;
          this.valueModal.loading = false;
          this.valueModal.error = null;
          this.valueModal.updateError = null;
          this.valueModal.value = '';
          this.valueModal.rawValue = null;
          this.valueModal.table = '';
          this.valueModal.column = '';
          this.valueModal.searchTerm = '';
          this.valueModal.editing = false;
          this.valueModal.editValue = '';
          this.valueModal.saving = false;
          this.valueModal.identifiers = [];
          this.valueModal.foreignKey = null;
          this.valueModal.foreignRecords = createForeignRecordsState();
        },
        toggleSort(table, column) {
          if (table.records.loading) {
            return;
          }

          if (table.records.sort === column) {
            table.records.direction = table.records.direction === 'asc' ? 'desc' : 'asc';
          } else {
            table.records.sort = column;
            table.records.direction = 'asc';
          }

          table.records.page = 1;
          this.loadRecords(table);
        },
        addFilter(table) {
          if (table.records.loading) {
            return;
          }

          const fallbackColumn = Array.isArray(table.records.columns) && table.records.columns.length > 0
            ? table.records.columns[0]
            : (Array.isArray(table.structure?.columns) && table.structure.columns.length > 0
              ? table.structure.columns[0].name
              : '');

          table.records.filters = [
            ...table.records.filters,
            {
              id: this.generateFilterId(),
              column: fallbackColumn || '',
              operator: '=',
              value: '',
            },
          ];
        },
        removeFilter(table, index) {
          if (table.records.loading) {
            return;
          }

          if (index < 0 || index >= table.records.filters.length) {
            return;
          }

          table.records.filters = table.records.filters.filter((_, filterIndex) => filterIndex !== index);

          if (table.records.loaded) {
            table.records.page = 1;
            this.loadRecords(table);
          }
        },
        applyFilters(table) {
          if (table.records.loading) {
            return;
          }

          table.records.page = 1;
          this.loadRecords(table);
        },
        resetFilters(table) {
          if (table.records.loading || table.records.filters.length === 0) {
            return;
          }

          table.records.filters = [];
          table.records.page = 1;
          this.loadRecords(table);
        },
        updateSearch(table, value) {
          const rawValue = typeof value === 'string' ? value : '';
          const normalized = rawValue.trim();

          table.records.searchInput = rawValue;

          if (table.records.search === normalized && table.records.page === 1 && table.records.loaded) {
            return;
          }

          table.records.search = normalized;
          table.records.page = 1;
          this.loadRecords(table);
        },
        updateSearchColumn(table, value) {
          const normalized = typeof value === 'string' ? value.trim() : '';
          const previous = typeof table.records.searchColumn === 'string'
            ? table.records.searchColumn.trim()
            : '';

          if (previous === normalized && table.records.page === 1 && table.records.loaded) {
            table.records.searchColumn = normalized;
            return;
          }

          table.records.searchColumn = normalized;
          table.records.page = 1;
          this.loadRecords(table);
        },
        normalizeFilters(filters, previousFilters = []) {
          if (!Array.isArray(filters)) {
            return [];
          }

          const idsByKey = new Map();

          previousFilters.forEach((filter) => {
            if (!filter) {
              return;
            }

            const key = this.filterKey(filter);
            const existing = idsByKey.get(key) || [];
            existing.push(filter.id);
            idsByKey.set(key, existing);
          });

          return filters
            .filter((filter) => filter && typeof filter === 'object')
            .map((filter) => {
              const column = typeof filter.column === 'string' ? filter.column : '';
              const operator = typeof filter.operator === 'string' ? filter.operator : '=';
              const rawValue = filter.value ?? '';
              const value = rawValue === null ? '' : String(rawValue);
              const key = this.filterKey({ column, operator, value });
              const pool = idsByKey.get(key) || [];
              const id = pool.shift() ?? this.generateFilterId();

              if (pool.length > 0) {
                idsByKey.set(key, pool);
              } else {
                idsByKey.delete(key);
              }

              return {
                id,
                column,
                operator,
                value,
              };
            });
        },
        filterKey(filter) {
          const column = typeof filter.column === 'string' ? filter.column : '';
          const operator = typeof filter.operator === 'string' ? filter.operator : '';
          const value = filter.value === undefined || filter.value === null ? '' : String(filter.value);

          return [column, operator, value].join('::');
        },
        operatorRequiresValue(operator) {
          const normalized = (operator ?? '').toString().toLowerCase();

          return ['=', '!=', '<', '<=', '>', '>=', 'like', 'not like', '<>'].includes(normalized);
        },
        generateFilterId() {
          if (window.crypto && typeof window.crypto.randomUUID === 'function') {
            return window.crypto.randomUUID();
          }

          return `filter-${Date.now()}-${Math.random().toString(16).slice(2)}`;
        },
        formatCell(value) {
          if (value === null || value === undefined) {
            return '—';
          }

          if (typeof value === 'object') {
            try {
              return JSON.stringify(value);
            } catch (error) {
              return '[object]';
            }
          }

          return String(value);
        },
        prepareEditableValue(value) {
          if (value === null || value === undefined) {
            return '';
          }

          if (typeof value === 'object') {
            try {
              return JSON.stringify(value, null, 2);
            } catch (error) {
              return String(value);
            }
          }

          return String(value);
        },
        truncateText(value, limit = 120) {
          const text = value === null || value === undefined ? '' : String(value);
          const numericLimit = Number(limit);

          if (!Number.isFinite(numericLimit) || numericLimit <= 0) {
            return text;
          }

          if (text.length <= numericLimit) {
            return text;
          }

          const ellipsis = '…';
          const sliceLength = Math.max(0, numericLimit - ellipsis.length);

          return `${text.slice(0, sliceLength)}${ellipsis}`;
        },
        renderRecordPreview(table, column, value) {
          const text = this.formatCell(value);
          const truncated = this.truncateText(text, this.cellPreviewLimit);
          const records = table && typeof table === 'object' ? table.records || {} : {};
          const searchTerm = typeof records.search === 'string' ? records.search : '';
          const selectedColumn = typeof records.searchColumn === 'string' ? records.searchColumn.trim() : '';
          const columnName = typeof column === 'string' ? column.trim() : '';

          if (!searchTerm || (selectedColumn && selectedColumn !== columnName)) {
            return this.escapeHtml(truncated);
          }

          return this.highlightText(truncated, searchTerm);
        },
        highlightQuery(value) {
          return this.highlightText(value, this.query);
        },
        highlightForeignRecordText(value) {
          const query =
            this.valueModal && this.valueModal.foreignRecords && typeof this.valueModal.foreignRecords.query === 'string'
              ? this.valueModal.foreignRecords.query
              : '';

          return this.highlightText(value, query);
        },
        highlightText(value, term) {
          const stringValue = value === null || value === undefined ? '' : String(value);
          const searchTerm = typeof term === 'string' ? term.trim() : '';

          if (!searchTerm) {
            return this.escapeHtml(stringValue);
          }

          const escapedSearch = this.escapeRegExp(searchTerm);

          if (!escapedSearch) {
            return this.escapeHtml(stringValue);
          }

          const regex = new RegExp(`(${escapedSearch})`, 'gi');
          const parts = stringValue.split(regex);

          return parts
            .map((part, index) => {
              if (index % 2 === 1 && part.length > 0) {
                return `<mark class="rounded bg-amber-200/70 px-1 py-0.5 text-amber-900">${this.escapeHtml(part)}</mark>`;
              }

              return this.escapeHtml(part);
            })
            .join('');
        },
        escapeHtml(value) {
          return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
        },
        escapeRegExp(value) {
          return String(value).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        },
      };
    };

    document.addEventListener('alpine:init', () => {
      if (window.Alpine && typeof window.Alpine.data === 'function') {
        window.Alpine.data('databaseStructureViewer', window.databaseStructureViewer);
      }
    });
  </script>
@endpush
