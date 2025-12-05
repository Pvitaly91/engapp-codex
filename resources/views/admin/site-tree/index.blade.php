@extends('layouts.app')

@section('title', 'Структура сайту')

@section('content')
    <div class="max-w-5xl mx-auto space-y-4" x-data="siteTreeEditor()" x-init="init()">
        {{-- Header with actions --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold">Структура сайту</h1>
                <p class="text-sm text-gray-500">Редагуйте структуру сайту: перетягуйте, перейменовуйте, створюйте та видаляйте елементи.</p>
            </div>
        </div>

        {{-- Variant Selector --}}
        <div class="flex flex-wrap items-center gap-2 p-3 bg-white border border-gray-200 rounded-xl shadow-sm">
            <div class="flex items-center gap-2">
                <label class="text-sm font-medium text-gray-700">Варіант:</label>
                <select 
                    x-model="currentVariantSlug" 
                    @change="switchVariant()" 
                    class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
                    <template x-for="variant in variants" :key="variant.id">
                        <option :value="variant.slug" :selected="variant.slug === currentVariantSlug" x-text="variant.name + (variant.is_base ? ' (базовий)' : '')"></option>
                    </template>
                </select>
            </div>
            
            <div class="h-6 w-px bg-gray-300 hidden sm:block"></div>
            
            <button 
                type="button"
                @click="showCreateVariantModal()"
                class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-green-700 bg-green-50 rounded-lg hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-green-400 focus:ring-offset-1 transition"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span class="hidden sm:inline">Новий варіант</span>
            </button>

            <template x-if="currentVariant && !currentVariant.is_base">
                <div class="flex items-center gap-1">
                    <button 
                        type="button"
                        @click="showEditVariantModal()"
                        class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-1 transition"
                        title="Перейменувати варіант"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </button>
                    <button 
                        type="button"
                        @click="deleteVariant()"
                        class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-400 focus:ring-offset-1 transition"
                        title="Видалити варіант"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>
            </template>
            
            <div class="flex-1"></div>
            
            {{-- Copy URL button --}}
            <template x-if="currentVariant && !currentVariant.is_base">
                <button 
                    type="button"
                    @click="copyVariantUrl()"
                    class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-blue-700 bg-blue-50 rounded-lg hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-1 transition"
                    title="Копіювати посилання на варіант"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    <span class="hidden sm:inline">Копіювати URL</span>
                </button>
            </template>
        </div>

        {{-- Toolbar --}}
        <div class="flex flex-wrap items-center gap-2 p-3 bg-white border border-gray-200 rounded-xl shadow-sm">
            <button 
                type="button"
                @click="addRootItem()"
                class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 transition"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span class="hidden sm:inline">Додати розділ</span>
                <span class="sm:hidden">Додати</span>
            </button>

            <div class="h-6 w-px bg-gray-300 hidden sm:block"></div>

            <button 
                type="button"
                @click="expandAll()"
                class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-1 transition"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                </svg>
                <span class="hidden sm:inline">Розгорнути все</span>
            </button>

            <button 
                type="button"
                @click="collapseAll()"
                class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-1 transition"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 9V4.5M9 9H4.5M9 9L3.75 3.75M9 15v4.5M9 15H4.5M9 15l-5.25 5.25M15 9h4.5M15 9V4.5M15 9l5.25-5.25M15 15h4.5M15 15v4.5m0-4.5l5.25 5.25"/>
                </svg>
                <span class="hidden sm:inline">Згорнути все</span>
            </button>

            <div class="flex-1"></div>

            <button 
                type="button"
                @click="exportTree()"
                class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-1 transition"
                title="Експортувати дерево"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                <span class="hidden sm:inline">Експорт</span>
            </button>

            <label class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 cursor-pointer focus-within:ring-2 focus-within:ring-gray-400 focus-within:ring-offset-1 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                <span class="hidden sm:inline">Імпорт</span>
                <input type="file" accept=".json" @change="importTree($event)" class="sr-only">
            </label>

            <button
                type="button"
                @click="resetTree()"
                class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-400 focus:ring-offset-1 transition"
                title="Скинути до початкового стану"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                <span class="hidden sm:inline">Скинути</span>
            </button>
            <template x-if="currentVariant && !currentVariant.is_base">
                <button
                    type="button"
                    @click="syncMissingFromBase()"
                    class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-blue-700 bg-blue-50 rounded-lg hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-1 transition"
                    title="Додати бракуючі розділи з базового дерева"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    <span class="hidden sm:inline">Додати з бази</span>
                </button>
            </template>
        </div>

        {{-- Status messages --}}
        <div x-show="message" x-transition x-cloak class="p-3 rounded-lg text-sm" :class="messageType === 'success' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200'">
            <span x-text="message"></span>
        </div>

        {{-- Tree container --}}
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow">
            <div class="p-1 sm:p-3 md:p-5">
                <template x-if="loading">
                    <div class="flex items-center justify-center py-12">
                        <svg class="animate-spin h-8 w-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                </template>

                <template x-if="!loading && tree.length === 0">
                    <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 p-6 text-center text-sm text-gray-500">
                        Структура сайту порожня. Натисніть "Додати розділ" щоб почати.
                    </div>
                </template>

                <template x-if="!loading && tree.length > 0">
                    <div class="space-y-1" id="site-tree">
                        <template x-for="(item, index) in tree" :key="item.id">
                            <div>
                                <template x-if="true">
                                    <div>
                                        {{-- Tree item --}}
                                        <div 
                                            class="group py-1 px-0.5 sm:px-1 rounded-lg transition-colors"
                                            :class="{
                                                'bg-blue-50 border-2 border-blue-400 border-dashed': dragOverId === item.id,
                                                'hover:bg-gray-50': dragOverId !== item.id,
                                                'opacity-50': !item.is_checked,
                                                'bg-gray-100': isItemSelected(item.id)
                                            }"
                                            draggable="true"
                                            @dragstart="handleDragStart($event, item, null, index)"
                                            @dragend="handleDragEnd()"
                                            @dragover.prevent="handleDragOver($event, item)"
                                            @dragleave="handleDragLeave($event)"
                                            @drop.prevent="handleDrop($event, item, null, index)"
                                            data-tree-item
                                        >
                                            <div class="flex items-center gap-0.5">
                                                {{-- Expand/collapse --}}
                                                <button 
                                                    type="button"
                                                    @click="toggleCollapse(item.id)"
                                                    class="flex-shrink-0 p-0.5 text-gray-400 hover:text-gray-600 transition"
                                                    x-show="item.children && item.children.length > 0"
                                                >
                                                    <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-90': !isCollapsed(item.id) }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                    </svg>
                                                </button>
                                                <span x-show="!item.children || item.children.length === 0" class="w-5"></span>

                                                {{-- Checkbox --}}
                                                <input 
                                                    type="checkbox"
                                                    :checked="item.is_checked"
                                                    @change="toggleItem(item)"
                                                    class="flex-shrink-0 rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500 focus:ring-offset-0"
                                                >

                                                {{-- Title (editable) - clickable to show actions --}}
                                                <div class="flex-1 min-w-0 flex items-center gap-1 cursor-pointer ml-1" @click="toggleItemActions(item.id)" @dblclick="startEditing(item)">
                                                    {{-- Category number --}}
                                                    <span class="flex-shrink-0 text-sm font-bold text-gray-500 mr-1" x-text="getCategoryNumber(index)"></span>
                                                    
                                                    <template x-if="editingId !== item.id">
                                                        <span class="text-sm font-semibold leading-tight" :class="[isItemSelected(item.id) ? 'truncate' : '', item.is_checked ? '' : 'line-through text-gray-400', existsInPages(item.title) ? 'text-green-700 bg-green-50 px-1 rounded' : '']" x-text="item.title"></span>
                                                    </template>
                                                    <template x-if="editingId === item.id">
                                                        <input 
                                                            type="text"
                                                            x-model="editingTitle"
                                                            @blur="saveEditing(item)"
                                                            @keydown.enter="saveEditing(item)"
                                                            @keydown.escape="cancelEditing()"
                                                            x-ref="editInput"
                                                            @click.stop
                                                            class="flex-1 px-2 py-1 text-sm border border-blue-400 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                        >
                                                    </template>

                                                    {{-- Level badge --}}
                                                    <template x-if="item.level">
                                                        <span class="flex-shrink-0 inline-flex items-center rounded-full bg-blue-100 px-1.5 py-0.5 text-xs font-medium text-blue-700" x-text="item.level"></span>
                                                    </template>
                                                    
                                                    {{-- Exists in pages indicator with link --}}
                                                    <template x-if="existsInPages(item.title)">
                                                        <a :href="getPageUrl(item.title)" target="_blank" class="flex-shrink-0 inline-flex items-center rounded-full bg-green-100 px-1.5 py-0.5 text-xs font-medium text-green-700 hover:bg-green-200 transition" title="Відкрити на сайті">✓</a>
                                                    </template>
                                                </div>

                                                {{-- Actions - visible when selected --}}
                                                <div class="flex-shrink-0 flex items-center gap-0.5 transition-all" :class="isItemSelected(item.id) ? 'opacity-100 w-auto' : 'opacity-0 w-0 overflow-hidden pointer-events-none'">
                                                    <button
                                                        type="button"
                                                        @click.stop="copySeederPrompt(item)"
                                                        class="p-1 text-gray-400 hover:text-purple-600 hover:bg-purple-50 rounded transition"
                                                        title="Згенерувати промт сидера"
                                                    >
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7h2a2 2 0 012 2v9a2 2 0 01-2 2H7a2 2 0 01-2-2V9a2 2 0 012-2h2" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 7V5a2 2 0 00-2-2H9a2 2 0 00-2 2v2m7 4H9" />
                                                        </svg>
                                                    </button>
                                                    {{-- Link to page on site - only for existing pages --}}
                                                    <template x-if="existsInPages(item.title)">
                                                        <a 
                                                            :href="getPageUrl(item.title)"
                                                            target="_blank"
                                                            @click.stop
                                                            class="p-1 text-green-500 hover:text-green-700 hover:bg-green-50 rounded transition"
                                                            title="Відкрити на сайті"
                                                        >
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                                            </svg>
                                                        </a>
                                                    </template>
                                                    <button 
                                                        type="button"
                                                        @click.stop="startEditing(item)"
                                                        class="p-1 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded transition"
                                                        title="Редагувати"
                                                    >
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                        </svg>
                                                    </button>
                                                    <button 
                                                        type="button"
                                                        @click.stop="addChild(item)"
                                                        class="p-1 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded transition"
                                                        title="Додати підрозділ"
                                                    >
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                        </svg>
                                                    </button>
                                                    <button 
                                                        type="button"
                                                        @click.stop="deleteItem(item)"
                                                        class="p-1 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded transition"
                                                        title="Видалити"
                                                    >
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                    </button>
                                                    {{-- Drag handle - only shown when selected --}}
                                                    <div class="cursor-grab active:cursor-grabbing p-1 text-gray-400 hover:text-gray-600 touch-manipulation" @touchstart="handleTouchStart($event, item, null, index)" @touchmove.prevent="handleTouchMove($event)" @touchend="handleTouchEnd($event)">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
                                                        </svg>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Children --}}
                                        <div x-show="!isCollapsed(item.id) && item.children && item.children.length > 0" x-collapse class="ml-2 sm:ml-4 border-l-2 border-gray-200 pl-1">
                                            <template x-for="(child, childIndex) in item.children" :key="child.id">
                                                <div>
                                                    {{-- Level 1 child --}}
                                                    <div 
                                                        class="group py-1 px-0.5 sm:px-1 rounded-lg transition-colors"
                                                        :class="{
                                                            'bg-blue-50 border-2 border-blue-400 border-dashed': dragOverId === child.id,
                                                            'hover:bg-gray-50': dragOverId !== child.id,
                                                            'opacity-50': !child.is_checked,
                                                            'bg-gray-100': isItemSelected(child.id)
                                                        }"
                                                        draggable="true"
                                                        @dragstart="handleDragStart($event, child, item.id, childIndex)"
                                                        @dragend="handleDragEnd()"
                                                        @dragover.prevent="handleDragOver($event, child)"
                                                        @dragleave="handleDragLeave($event)"
                                                        @drop.prevent="handleDrop($event, child, item.id, childIndex)"
                                                        data-tree-item
                                                    >
                                                        <div class="flex items-center gap-0.5">
                                                            <button 
                                                                type="button"
                                                                @click="toggleCollapse(child.id)"
                                                                class="flex-shrink-0 p-0.5 text-gray-400 hover:text-gray-600 transition"
                                                                x-show="child.children && child.children.length > 0"
                                                            >
                                                                <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-90': !isCollapsed(child.id) }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                                </svg>
                                                            </button>
                                                            <span x-show="!child.children || child.children.length === 0" class="w-5"></span>

                                                            <input 
                                                                type="checkbox"
                                                                :checked="child.is_checked"
                                                                @change="toggleItem(child)"
                                                                class="flex-shrink-0 rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500 focus:ring-offset-0"
                                                            >

                                                            <div class="flex-1 min-w-0 flex items-center gap-1 cursor-pointer ml-1" @click="toggleItemActions(child.id)" @dblclick="startEditing(child)">
                                                                {{-- Page number within category --}}
                                                                <span class="flex-shrink-0 text-xs font-medium text-gray-400 mr-1" x-text="getItemNumber(index, childIndex)"></span>
                                                                
                                                                <template x-if="editingId !== child.id">
                                                                    <span class="text-sm leading-tight" :class="[isItemSelected(child.id) ? 'truncate' : '', child.is_checked ? '' : 'line-through text-gray-400', existsInPages(child.title) ? 'text-green-700 bg-green-50 px-1 rounded' : '']" x-text="child.title"></span>
                                                                </template>
                                                                <template x-if="editingId === child.id">
                                                                    <input 
                                                                        type="text"
                                                                        x-model="editingTitle"
                                                                        @blur="saveEditing(child)"
                                                                        @keydown.enter="saveEditing(child)"
                                                                        @keydown.escape="cancelEditing()"
                                                                        @click.stop
                                                                        class="flex-1 px-2 py-1 text-sm border border-blue-400 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                                    >
                                                                </template>
                                                                <template x-if="child.level">
                                                                    <span class="flex-shrink-0 inline-flex items-center rounded-full bg-blue-100 px-1.5 py-0.5 text-xs font-medium text-blue-700" x-text="child.level"></span>
                                                                </template>
                                                                
                                                                {{-- Exists in pages indicator with link --}}
                                                                <template x-if="existsInPages(child.title)">
                                                                    <a :href="getPageUrl(child.title)" target="_blank" class="flex-shrink-0 inline-flex items-center rounded-full bg-green-100 px-1.5 py-0.5 text-xs font-medium text-green-700 hover:bg-green-200 transition" title="Відкрити на сайті">✓</a>
                                                                </template>
                                                            </div>

                                                            <div class="flex-shrink-0 flex items-center gap-0.5 transition-all" :class="isItemSelected(child.id) ? 'opacity-100 w-auto' : 'opacity-0 w-0 overflow-hidden pointer-events-none'">
                                                                <button type="button" @click.stop="copySeederPrompt(child)" class="p-1 text-gray-400 hover:text-purple-600 hover:bg-purple-50 rounded transition" title="Згенерувати промт сидера">
                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7h2a2 2 0 012 2v9a2 2 0 01-2 2H7a2 2 0 01-2-2V9a2 2 0 012-2h2" />
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 7V5a2 2 0 00-2-2H9a2 2 0 00-2 2v2m7 4H9" />
                                                                    </svg>
                                                                </button>
                                                                {{-- Link to page on site --}}
                                                                <template x-if="existsInPages(child.title)">
                                                                    <a :href="getPageUrl(child.title)" target="_blank" @click.stop class="p-1 text-green-500 hover:text-green-700 hover:bg-green-50 rounded transition" title="Відкрити на сайті">
                                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                                                    </a>
                                                                </template>
                                                                <button type="button" @click.stop="startEditing(child)" class="p-1 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded transition" title="Редагувати">
                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                                </button>
                                                                <button type="button" @click.stop="addChild(child)" class="p-1 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded transition" title="Додати підрозділ">
                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                                </button>
                                                                <button type="button" @click.stop="deleteItem(child)" class="p-1 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded transition" title="Видалити">
                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                                </button>
                                                                <div class="cursor-grab active:cursor-grabbing p-1 text-gray-400 hover:text-gray-600 touch-manipulation" @touchstart="handleTouchStart($event, child, item.id, childIndex)" @touchmove.prevent="handleTouchMove($event)" @touchend="handleTouchEnd($event)">
                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/></svg>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- Level 2 children --}}
                                                    <div x-show="!isCollapsed(child.id) && child.children && child.children.length > 0" x-collapse class="ml-2 sm:ml-4 border-l-2 border-gray-200 pl-1">
                                                        <template x-for="(grandchild, grandchildIndex) in child.children" :key="grandchild.id">
                                                            <div 
                                                                class="group py-1 px-0.5 sm:px-1 rounded-lg transition-colors"
                                                                :class="{
                                                                    'bg-blue-50 border-2 border-blue-400 border-dashed': dragOverId === grandchild.id,
                                                                    'hover:bg-gray-50': dragOverId !== grandchild.id,
                                                                    'opacity-50': !grandchild.is_checked,
                                                                    'bg-gray-100': isItemSelected(grandchild.id)
                                                                }"
                                                                draggable="true"
                                                                @dragstart="handleDragStart($event, grandchild, child.id, grandchildIndex)"
                                                                @dragend="handleDragEnd()"
                                                                @dragover.prevent="handleDragOver($event, grandchild)"
                                                                @dragleave="handleDragLeave($event)"
                                                                @drop.prevent="handleDrop($event, grandchild, child.id, grandchildIndex)"
                                                                data-tree-item
                                                            >
                                                                <div class="flex items-center gap-0.5">
                                                                    <span class="w-5"></span>

                                                                    <input 
                                                                        type="checkbox"
                                                                        :checked="grandchild.is_checked"
                                                                        @change="toggleItem(grandchild)"
                                                                        class="flex-shrink-0 rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500 focus:ring-offset-0"
                                                                    >

                                                                    <div class="flex-1 min-w-0 flex items-center gap-1 cursor-pointer ml-1" @click="toggleItemActions(grandchild.id)" @dblclick="startEditing(grandchild)">
                                                                        <template x-if="editingId !== grandchild.id">
                                                                            <span class="text-sm leading-tight" :class="[isItemSelected(grandchild.id) ? 'truncate' : '', grandchild.is_checked ? '' : 'line-through text-gray-400', existsInPages(grandchild.title) ? 'text-green-700 bg-green-50 px-1 rounded' : '']" x-text="grandchild.title"></span>
                                                                        </template>
                                                                        <template x-if="editingId === grandchild.id">
                                                                            <input 
                                                                                type="text"
                                                                                x-model="editingTitle"
                                                                                @blur="saveEditing(grandchild)"
                                                                                @keydown.enter="saveEditing(grandchild)"
                                                                                @keydown.escape="cancelEditing()"
                                                                                @click.stop
                                                                                class="flex-1 px-2 py-1 text-sm border border-blue-400 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                                            >
                                                                        </template>
                                                                        <template x-if="grandchild.level">
                                                                            <span class="flex-shrink-0 inline-flex items-center rounded-full bg-blue-100 px-1.5 py-0.5 text-xs font-medium text-blue-700" x-text="grandchild.level"></span>
                                                                        </template>
                                                                        
                                                                        {{-- Exists in pages indicator with link --}}
                                                                        <template x-if="existsInPages(grandchild.title)">
                                                                            <a :href="getPageUrl(grandchild.title)" target="_blank" class="flex-shrink-0 inline-flex items-center rounded-full bg-green-100 px-1.5 py-0.5 text-xs font-medium text-green-700 hover:bg-green-200 transition" title="Відкрити на сайті">✓</a>
                                                                        </template>
                                                                    </div>

                                                                    <div class="flex-shrink-0 flex items-center gap-0.5 transition-all" :class="isItemSelected(grandchild.id) ? 'opacity-100 w-auto' : 'opacity-0 w-0 overflow-hidden pointer-events-none'">
                                                                        <button type="button" @click.stop="copySeederPrompt(grandchild)" class="p-1 text-gray-400 hover:text-purple-600 hover:bg-purple-50 rounded transition" title="Згенерувати промт сидера">
                                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7h2a2 2 0 012 2v9a2 2 0 01-2 2H7a2 2 0 01-2-2V9a2 2 0 012-2h2" />
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 7V5a2 2 0 00-2-2H9a2 2 0 00-2 2v2m7 4H9" />
                                                                            </svg>
                                                                        </button>
                                                                        {{-- Link to page on site --}}
                                                                        <template x-if="existsInPages(grandchild.title)">
                                                                            <a :href="getPageUrl(grandchild.title)" target="_blank" @click.stop class="p-1 text-green-500 hover:text-green-700 hover:bg-green-50 rounded transition" title="Відкрити на сайті">
                                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                                                            </a>
                                                                        </template>
                                                                        <button type="button" @click.stop="startEditing(grandchild)" class="p-1 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded transition" title="Редагувати">
                                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                                        </button>
                                                                        <button type="button" @click.stop="deleteItem(grandchild)" class="p-1 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded transition" title="Видалити">
                                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                                        </button>
                                                                        <div class="cursor-grab active:cursor-grabbing p-1 text-gray-400 hover:text-gray-600 touch-manipulation" @touchstart="handleTouchStart($event, grandchild, child.id, grandchildIndex)" @touchmove.prevent="handleTouchMove($event)" @touchend="handleTouchEnd($event)">
                                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/></svg>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </div>

        {{-- Add/Edit Modal --}}
        <div 
            x-show="showModal"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
            @click.self="closeModal()"
        >
            <div 
                class="w-full max-w-md bg-white rounded-2xl shadow-xl"
                @click.stop
            >
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4" x-text="modalTitle"></h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Назва</label>
                            <input 
                                type="text"
                                x-model="modalData.title"
                                @keydown.enter="submitModal()"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Введіть назву"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Рівень (опціонально)</label>
                            <input 
                                type="text"
                                x-model="modalData.level"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="напр. A1, A2–B1, B2"
                            >
                        </div>
                    </div>
                </div>
                <div class="flex justify-end gap-2 px-6 py-4 bg-gray-50 rounded-b-2xl">
                    <button 
                        type="button"
                        @click="closeModal()"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400"
                    >
                        Скасувати
                    </button>
                    <button 
                        type="button"
                        @click="submitModal()"
                        :disabled="!modalData.title.trim()"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span x-text="modalSubmitText"></span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Confirm Reset Modal --}}
        <div 
            x-show="showResetConfirm" 
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
            @click.self="showResetConfirm = false"
        >
            <div class="w-full max-w-md bg-white rounded-2xl shadow-xl p-6">
                <h3 class="text-lg font-semibold mb-2 text-red-600">Підтвердження скидання</h3>
                <p class="text-sm text-gray-600 mb-4">Ви впевнені, що хочете скинути дерево до початкового стану? Усі ваші зміни будуть втрачені.</p>
                <div class="flex justify-end gap-2">
                    <button 
                        type="button"
                        @click="showResetConfirm = false"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                    >
                        Скасувати
                    </button>
                    <button 
                        type="button"
                        @click="confirmReset()"
                        class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700"
                    >
                        Скинути
                    </button>
                </div>
            </div>
        </div>

        {{-- Variant Modal --}}
        <div 
            x-show="showVariantModal"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
            @click.self="closeVariantModal()"
        >
            <div 
                class="w-full max-w-md bg-white rounded-2xl shadow-xl"
                @click.stop
            >
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4" x-text="variantModalTitle"></h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Назва варіанту</label>
                            <input 
                                type="text"
                                x-model="variantModalData.name"
                                @keydown.enter="submitVariantModal()"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Введіть назву"
                            >
                        </div>
                    </div>
                </div>
                <div class="flex justify-end gap-2 px-6 py-4 bg-gray-50 rounded-b-2xl">
                    <button 
                        type="button"
                        @click="closeVariantModal()"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400"
                    >
                        Скасувати
                    </button>
                    <button 
                        type="button"
                        @click="submitVariantModal()"
                        :disabled="!variantModalData.name.trim()"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span x-text="variantModalSubmitText"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function siteTreeEditor() {
            return {
                tree: @json($tree),
                variants: @json($variants ?? []),
                currentVariant: @json($currentVariant ?? null),
                currentVariantId: {{ $currentVariant->id ?? 'null' }},
                currentVariantSlug: '{{ $currentVariant->slug ?? "" }}',
                existingPages: @json($existingPages ?? []),
                loading: false,
                message: '',
                messageType: 'success',
                editingId: null,
                editingTitle: '',
                showModal: false,
                modalTitle: '',
                modalSubmitText: 'Зберегти',
                modalData: { title: '', level: '', parentId: null },
                modalCallback: null,
                showResetConfirm: false,
                showVariantModal: false,
                variantModalTitle: '',
                variantModalSubmitText: 'Створити',
                variantModalData: { name: '', id: null },
                variantModalMode: 'create',
                draggedItem: null,
                draggedParentId: null,
                draggedIndex: null,
                dragOverId: null,
                csrfToken: null,
                touchDragItem: null,
                touchStartY: 0,
                collapsedItems: {},
                selectedItemId: null,

                init() {
                    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                    this.csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : null;
                    
                    // Close actions when clicking outside
                    document.addEventListener('click', (e) => {
                        if (!e.target.closest('[data-tree-item]')) {
                            this.selectedItemId = null;
                        }
                    });
                },
                
                // Variant methods
                switchVariant() {
                    if (this.currentVariantSlug) {
                        const variant = this.variants.find(v => v.slug === this.currentVariantSlug);
                        if (variant) {
                            if (variant.is_base) {
                                window.location.href = '/admin/site-tree';
                            } else {
                                window.location.href = '/admin/site-tree/variant/' + variant.slug;
                            }
                        }
                    }
                },
                
                showCreateVariantModal() {
                    this.variantModalTitle = 'Новий варіант дерева';
                    this.variantModalSubmitText = 'Створити';
                    this.variantModalData = { name: '', id: null };
                    this.variantModalMode = 'create';
                    this.showVariantModal = true;
                },
                
                showEditVariantModal() {
                    if (!this.currentVariant) return;
                    this.variantModalTitle = 'Редагувати варіант';
                    this.variantModalSubmitText = 'Зберегти';
                    this.variantModalData = { name: this.currentVariant.name, id: this.currentVariant.id };
                    this.variantModalMode = 'edit';
                    this.showVariantModal = true;
                },
                
                closeVariantModal() {
                    this.showVariantModal = false;
                    this.variantModalData = { name: '', id: null };
                },
                
                async submitVariantModal() {
                    if (!this.variantModalData.name.trim()) return;
                    
                    try {
                        if (this.variantModalMode === 'create') {
                            const response = await fetch('/admin/site-tree-variants', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': this.csrfToken,
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({ name: this.variantModalData.name.trim() })
                            });
                            const data = await response.json();
                            if (data.success) {
                                window.location.href = data.url;
                            }
                        } else {
                            const response = await fetch('/admin/site-tree-variants/' + this.variantModalData.id, {
                                method: 'PUT',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': this.csrfToken,
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({ name: this.variantModalData.name.trim() })
                            });
                            const data = await response.json();
                            if (data.success) {
                                this.currentVariant.name = data.variant.name;
                                const idx = this.variants.findIndex(v => v.id === data.variant.id);
                                if (idx !== -1) {
                                    this.variants[idx].name = data.variant.name;
                                }
                                this.showMessage('Варіант оновлено');
                                this.closeVariantModal();
                            }
                        }
                    } catch (error) {
                        this.showMessage('Помилка збереження варіанту', 'error');
                    }
                },
                
                async deleteVariant() {
                    if (!this.currentVariant || this.currentVariant.is_base) return;
                    if (!confirm('Видалити цей варіант дерева? Усі його дані будуть втрачені.')) return;
                    
                    try {
                        const response = await fetch('/admin/site-tree-variants/' + this.currentVariant.id, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': this.csrfToken,
                                'Accept': 'application/json'
                            }
                        });
                        const data = await response.json();
                        if (data.success) {
                            window.location.href = '/admin/site-tree';
                        }
                    } catch (error) {
                        this.showMessage('Помилка видалення варіанту', 'error');
                    }
                },
                
                copyVariantUrl() {
                    if (!this.currentVariant) return;
                    const url = window.location.origin + '/admin/site-tree/variant/' + this.currentVariant.slug;
                    navigator.clipboard.writeText(url).then(() => {
                        this.showMessage('URL скопійовано');
                    }).catch(() => {
                        this.showMessage('Не вдалося скопіювати URL', 'error');
                    });
                },
                
                toggleItemActions(itemId) {
                    this.selectedItemId = this.selectedItemId === itemId ? null : itemId;
                },
                
                isItemSelected(itemId) {
                    return this.selectedItemId === itemId;
                },
                
                existsInPages(title) {
                    // Check if the title exists among /theory categories or pages (existingPages is title -> url)
                    const cleanTitle = title.replace(/^\d+\.\s*/, '').replace(/^\d+\.\d+\s*/, '');
                    const titles = Object.keys(this.existingPages);
                    return titles.some(t => t === title || t === cleanTitle || title.includes(t) || t.includes(cleanTitle));
                },
                
                getPageUrl(title) {
                    // Get the URL for a page if it exists
                    const cleanTitle = title.replace(/^\d+\.\s*/, '').replace(/^\d+\.\d+\s*/, '');
                    const titles = Object.keys(this.existingPages);
                    for (const t of titles) {
                        if (t === title || t === cleanTitle || title.includes(t) || t.includes(cleanTitle)) {
                            return this.existingPages[t];
                        }
                    }
                    return null;
                },
                
                getCategoryNumber(index) {
                    return (index + 1) + '.';
                },
                
                getItemNumber(categoryIndex, itemIndex) {
                    return (categoryIndex + 1) + '.' + (itemIndex + 1);
                },

                getItemPath(itemId, items = this.tree, path = []) {
                    for (const item of items) {
                        const currentPath = [...path, item.title];
                        if (item.id === itemId) {
                            return currentPath;
                        }

                        if (item.children && item.children.length > 0) {
                            const foundPath = this.getItemPath(itemId, item.children, currentPath);
                            if (foundPath.length) {
                                return foundPath;
                            }
                        }
                    }
                    return [];
                },

                slugifyTitle(title) {
                    return title
                        .toLowerCase()
                        .replace(/[^a-z0-9\s-]+/g, '')
                        .trim()
                        .replace(/\s+/g, '-')
                        .replace(/-+/g, '-');
                },

                buildSeederPrompt(item) {
                    const path = this.getItemPath(item.id);
                    const topic = path.join(' > ');
                    const parentPath = path.slice(0, -1).join(' > ');
                    const slug = this.slugifyTitle(item.title || '');
                    const isCategory = Array.isArray(item.children) && item.children.length > 0;

                    if (isCategory) {
                        return [
                            '- Створи PHP сидер категорії у папці database/seeders/Page_v2 (/Pages_V2) за аналогією з іншими сидерами.',
                            `- Категорія: "${topic}". Вона має відповідати розташуванню у дереві та вкладеності інших категорій.`,
                            `- Використай slug з латиницею: ${slug || '[вкажи slug латиницею]'} у методі slug().`,
                            '- Додай title і tags для категорії, дотримуйся стилю існуючих категорійних сидерів.',
                            '- Мова назв і тегів — українська, без плейсхолдерів.',
                        ].join('\n');
                    }

                    return [
                        '- Створи PHP сидер сторінки у папці database/seeders/Page_v2 (/Pages_V2), за аналогією з іншими сидерами.',
                        '- Сторінка має бути в тій самій категорійній гілці, що й у дереві (врахуй шлях батьківських категорій у структурі папок).',
                        parentPath ? `- Категорія сторінки: "${parentPath}".` : '- Це сторінка верхнього рівня.',
                        `- Тип сторінки: theory (method type() має повертати \"theory\").`,
                        `- Тема сторінки: "${topic}". Використай цю тему як основу контенту та блоків.`,
                        `- Використай slug з латиницею: ${slug || '[вкажи slug латиницею]'} у методі slug().`,
                        '- Додай title, subtitle, релевантні tags (масив рядків) і blocks у page() за структурою інших сидерів Page_v2.',
                        '- Мова контенту — українська, без плейсхолдерів.',
                    ].join('\n');
                },

                copySeederPrompt(item) {
                    const prompt = this.buildSeederPrompt(item);
                    navigator.clipboard.writeText(prompt).then(() => {
                        this.showMessage('Промт сидера скопійовано');
                    }).catch(() => {
                        this.showMessage('Не вдалося скопіювати промт', 'error');
                    });
                },

                isCollapsed(itemId) {
                    return this.collapsedItems[itemId] === true;
                },

                toggleCollapse(itemId) {
                    this.collapsedItems[itemId] = !this.collapsedItems[itemId];
                },

                showMessage(text, type = 'success') {
                    this.message = text;
                    this.messageType = type;
                    setTimeout(() => { this.message = ''; }, 3000);
                },

                async fetchTree(showLoading = true) {
                    if (showLoading) {
                        this.loading = true;
                    }
                    try {
                        const url = this.currentVariantId ? `/admin/site-tree/api?variant_id=${this.currentVariantId}` : '/admin/site-tree/api';
                        const response = await fetch(url, {
                            headers: { 'Accept': 'application/json' }
                        });
                        const data = await response.json();
                        if (data.success) {
                            this.tree = data.tree;
                        }
                    } catch (error) {
                        this.showMessage('Помилка завантаження дерева', 'error');
                    }
                    if (showLoading) {
                        this.loading = false;
                    }
                },

                async toggleItem(item) {
                    try {
                        const response = await fetch(`/admin/site-tree/${item.id}/toggle`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ is_checked: !item.is_checked })
                        });
                        const data = await response.json();
                        if (data.success) {
                            item.is_checked = data.is_checked;
                        }
                    } catch (error) {
                        this.showMessage('Помилка збереження', 'error');
                    }
                },

                startEditing(item) {
                    this.editingId = item.id;
                    this.editingTitle = item.title;
                    // Focus will be handled by Alpine's x-init on the input
                    this.$nextTick(() => {
                        const inputs = document.querySelectorAll('input[type="text"]');
                        for (const input of inputs) {
                            if (input.value === item.title) {
                                input.focus();
                                input.select();
                                break;
                            }
                        }
                    });
                },

                async saveEditing(item) {
                    if (this.editingTitle.trim() === item.title) {
                        this.cancelEditing();
                        return;
                    }
                    
                    try {
                        const response = await fetch(`/admin/site-tree/${item.id}`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ title: this.editingTitle.trim() })
                        });
                        
                        if (!response.ok) {
                            const errorText = response.status === 422 ? 'Невірні дані' : 
                                              response.status === 404 ? 'Елемент не знайдено' :
                                              response.status >= 500 ? 'Помилка сервера' : 'Помилка збереження';
                            throw new Error(errorText);
                        }
                        
                        const data = await response.json();
                        if (data.success) {
                            item.title = data.item.title;
                            this.showMessage('Збережено');
                        }
                    } catch (error) {
                        this.showMessage(error.message || 'Помилка збереження', 'error');
                    }
                    this.cancelEditing();
                },

                cancelEditing() {
                    this.editingId = null;
                    this.editingTitle = '';
                },

                addRootItem() {
                    this.modalTitle = 'Новий розділ';
                    this.modalSubmitText = 'Створити';
                    this.modalData = { title: '', level: '', parentId: null };
                    this.showModal = true;
                },

                addChild(parent) {
                    this.modalTitle = 'Новий підрозділ';
                    this.modalSubmitText = 'Створити';
                    this.modalData = { title: '', level: '', parentId: parent.id };
                    this.showModal = true;
                },

                closeModal() {
                    this.showModal = false;
                    this.modalData = { title: '', level: '', parentId: null };
                },

                async submitModal() {
                    if (!this.modalData.title.trim()) return;

                    try {
                        const response = await fetch('/admin/site-tree', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                title: this.modalData.title.trim(),
                                level: this.modalData.level.trim() || null,
                                parent_id: this.modalData.parentId,
                                variant_id: this.currentVariantId
                            })
                        });
                        
                        if (!response.ok) {
                            throw new Error(response.status >= 500 ? 'Помилка сервера' : 'Помилка створення');
                        }
                        
                        const data = await response.json();
                        if (data.success) {
                            await this.fetchTree(false);
                            this.showMessage('Створено');
                            this.closeModal();
                        }
                    } catch (error) {
                        this.showMessage('Помилка створення', 'error');
                    }
                },

                async deleteItem(item) {
                    if (!confirm('Видалити цей елемент та всі його підрозділи?')) return;

                    try {
                        const response = await fetch(`/admin/site-tree/${item.id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': this.csrfToken,
                                'Accept': 'application/json'
                            }
                        });
                        const data = await response.json();
                        if (data.success) {
                            await this.fetchTree(false);
                            this.showMessage('Видалено');
                        }
                    } catch (error) {
                        this.showMessage('Помилка видалення', 'error');
                    }
                },

                resetTree() {
                    this.showResetConfirm = true;
                },

                async confirmReset() {
                    this.showResetConfirm = false;
                    this.loading = true;

                    try {
                        const response = await fetch('/admin/site-tree/reset', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                variant_id: this.currentVariantId
                            })
                        });
                        const data = await response.json();
                        if (data.success) {
                            await this.fetchTree();
                            this.showMessage('Дерево скинуто до початкового стану');
                        }
                    } catch (error) {
                        this.showMessage('Помилка скидання', 'error');
                    }
                    this.loading = false;
                },

                async syncMissingFromBase() {
                    if (!this.currentVariantId || (this.currentVariant && this.currentVariant.is_base)) return;

                    this.loading = true;
                    try {
                        const response = await fetch('/admin/site-tree/sync-missing', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ variant_id: this.currentVariantId })
                        });

                        const data = await response.json();

                        if (data.success) {
                            await this.fetchTree(false);
                            this.showMessage(data.message || 'Додано нові елементи з бази');
                        } else {
                            this.showMessage(data.message || 'Помилка оновлення дерева', 'error');
                        }
                    } catch (error) {
                        this.showMessage('Помилка оновлення дерева', 'error');
                    }

                    this.loading = false;
                },

                expandAll() {
                    // Collect all item IDs recursively and set them to expanded (not collapsed)
                    const collectIds = (items) => {
                        items.forEach(item => {
                            this.collapsedItems[item.id] = false;
                            if (item.children && item.children.length > 0) {
                                collectIds(item.children);
                            }
                        });
                    };
                    collectIds(this.tree);
                },

                collapseAll() {
                    // Collect all item IDs recursively and set them to collapsed
                    const collectIds = (items) => {
                        items.forEach(item => {
                            if (item.children && item.children.length > 0) {
                                this.collapsedItems[item.id] = true;
                                collectIds(item.children);
                            }
                        });
                    };
                    collectIds(this.tree);
                },

                async exportTree() {
                    try {
                        const url = this.currentVariantId ? `/admin/site-tree/export?variant_id=${this.currentVariantId}` : '/admin/site-tree/export';
                        const response = await fetch(url, {
                            headers: { 'Accept': 'application/json' }
                        });
                        const data = await response.json();
                        if (data.success) {
                            const blob = new Blob([JSON.stringify(data.tree, null, 2)], { type: 'application/json' });
                            const url = URL.createObjectURL(blob);
                            const a = document.createElement('a');
                            a.href = url;
                            a.download = 'site-tree-export.json';
                            a.click();
                            URL.revokeObjectURL(url);
                            this.showMessage('Експортовано');
                        }
                    } catch (error) {
                        this.showMessage('Помилка експорту', 'error');
                    }
                },

                async importTree(event) {
                    const file = event.target.files[0];
                    if (!file) return;

                    try {
                        const text = await file.text();
                        const tree = JSON.parse(text);
                        
                        if (!confirm('Імпортувати це дерево? Поточне дерево буде замінено.')) {
                            event.target.value = '';
                            return;
                        }

                        const response = await fetch('/admin/site-tree/import', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ tree, variant_id: this.currentVariantId })
                        });
                        const data = await response.json();
                        if (data.success) {
                            await this.fetchTree();
                            this.showMessage('Імпортовано');
                        }
                    } catch (error) {
                        this.showMessage('Помилка імпорту. Перевірте формат файлу.', 'error');
                    }
                    event.target.value = '';
                },

                // Drag and drop handlers
                handleDragStart(event, item, parentId, index) {
                    this.draggedItem = item;
                    this.draggedParentId = parentId;
                    this.draggedIndex = index;
                    event.dataTransfer.effectAllowed = 'move';
                    event.dataTransfer.setData('text/plain', item.id);
                },

                handleDragEnd() {
                    this.draggedItem = null;
                    this.draggedParentId = null;
                    this.draggedIndex = null;
                    this.dragOverId = null;
                },

                handleDragOver(event, item) {
                    if (!this.draggedItem || this.draggedItem.id === item.id) return;
                    this.dragOverId = item.id;
                },

                handleDragLeave(event) {
                    if (!event.currentTarget.contains(event.relatedTarget)) {
                        this.dragOverId = null;
                    }
                },

                async handleDrop(event, targetItem, targetParentId, targetIndex) {
                    if (!this.draggedItem || this.draggedItem.id === targetItem.id) {
                        this.dragOverId = null;
                        return;
                    }

                    // Prevent dropping a parent onto its own child
                    if (this.isDescendant(targetItem, this.draggedItem.id)) {
                        this.dragOverId = null;
                        this.showMessage('Неможливо перемістити елемент у власний підрозділ', 'error');
                        return;
                    }

                    // Calculate new position - drop after target (same parent as target)
                    const newParentId = targetParentId;
                    // We use array index since sort_order values are maintained on backend
                    const newSortOrder = targetIndex + 1;

                    try {
                        const response = await fetch(`/admin/site-tree/${this.draggedItem.id}/move`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                parent_id: newParentId,
                                sort_order: newSortOrder
                            })
                        });
                        const data = await response.json();
                        if (data.success) {
                            // Fetch tree without showing loading spinner to avoid visual reload effect
                            await this.fetchTree(false);
                            this.showMessage('Переміщено');
                        } else {
                            this.showMessage(data.message || 'Помилка переміщення', 'error');
                        }
                    } catch (error) {
                        this.showMessage('Помилка переміщення', 'error');
                    }

                    this.dragOverId = null;
                },

                // Check if checkItem is a descendant of itemId (iterative with depth limit)
                isDescendant(checkItem, itemId) {
                    if (!checkItem.children) return false;
                    const stack = [...checkItem.children];
                    let depth = 0;
                    const maxDepth = 20;
                    while (stack.length > 0 && depth < maxDepth) {
                        const item = stack.pop();
                        if (item.id === itemId) return true;
                        if (item.children) {
                            stack.push(...item.children);
                        }
                        depth++;
                    }
                    return false;
                },

                // Touch handlers for mobile
                handleTouchStart(event, item, parentId, index) {
                    this.touchDragItem = { item, parentId, index };
                    this.touchStartY = event.touches[0].clientY;
                },

                handleTouchMove(event) {
                    if (!this.touchDragItem) return;
                    // Visual feedback could be added here
                },

                handleTouchEnd(event) {
                    this.touchDragItem = null;
                }
            };
        }
    </script>
@endpush
