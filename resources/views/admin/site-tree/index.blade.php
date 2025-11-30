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
        </div>

        {{-- Status messages --}}
        <div x-show="message" x-transition x-cloak class="p-3 rounded-lg text-sm" :class="messageType === 'success' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200'">
            <span x-text="message"></span>
        </div>

        {{-- Tree container --}}
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow">
            <div class="p-4 sm:p-6">
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
                            <div x-data="{ collapsed: false }">
                                <template x-if="true">
                                    <div>
                                        {{-- Tree item --}}
                                        <div 
                                            class="group flex items-center gap-1 py-1.5 px-2 rounded-lg transition-colors"
                                            :class="{
                                                'bg-blue-50 border-2 border-blue-400 border-dashed': dragOverId === item.id,
                                                'hover:bg-gray-50': dragOverId !== item.id,
                                                'opacity-50': !item.is_checked
                                            }"
                                            draggable="true"
                                            @dragstart="handleDragStart($event, item, null, index)"
                                            @dragend="handleDragEnd()"
                                            @dragover.prevent="handleDragOver($event, item)"
                                            @dragleave="handleDragLeave($event)"
                                            @drop.prevent="handleDrop($event, item, null, index)"
                                        >
                                            {{-- Drag handle --}}
                                            <div class="flex-shrink-0 cursor-grab active:cursor-grabbing p-1 text-gray-400 hover:text-gray-600 touch-manipulation" @touchstart="handleTouchStart($event, item, null, index)" @touchmove.prevent="handleTouchMove($event)" @touchend="handleTouchEnd($event)">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
                                                </svg>
                                            </div>

                                            {{-- Expand/collapse --}}
                                            <button 
                                                type="button"
                                                @click="collapsed = !collapsed"
                                                class="flex-shrink-0 p-1 text-gray-400 hover:text-gray-600 transition"
                                                x-show="item.children && item.children.length > 0"
                                            >
                                                <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-90': !collapsed }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                </svg>
                                            </button>
                                            <span x-show="!item.children || item.children.length === 0" class="w-6"></span>

                                            {{-- Checkbox --}}
                                            <input 
                                                type="checkbox"
                                                :checked="item.is_checked"
                                                @change="toggleItem(item)"
                                                class="flex-shrink-0 rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500 focus:ring-offset-0"
                                            >

                                            {{-- Title (editable) --}}
                                            <div class="flex-1 min-w-0 flex items-center gap-2" @dblclick="startEditing(item)">
                                                <template x-if="editingId !== item.id">
                                                    <span class="text-sm truncate" :class="{ 'line-through text-gray-400': !item.is_checked }" x-text="item.title"></span>
                                                </template>
                                                <template x-if="editingId === item.id">
                                                    <input 
                                                        type="text"
                                                        x-model="editingTitle"
                                                        @blur="saveEditing(item)"
                                                        @keydown.enter="saveEditing(item)"
                                                        @keydown.escape="cancelEditing()"
                                                        x-ref="editInput"
                                                        class="flex-1 px-2 py-1 text-sm border border-blue-400 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                    >
                                                </template>

                                                {{-- Level badge --}}
                                                <template x-if="item.level">
                                                    <span class="flex-shrink-0 inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-700" x-text="item.level"></span>
                                                </template>
                                            </div>

                                            {{-- Actions --}}
                                            <div class="flex-shrink-0 flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <button 
                                                    type="button"
                                                    @click="startEditing(item)"
                                                    class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded transition"
                                                    title="Редагувати"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                </button>
                                                <button 
                                                    type="button"
                                                    @click="addChild(item)"
                                                    class="p-1.5 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded transition"
                                                    title="Додати підрозділ"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                    </svg>
                                                </button>
                                                <button 
                                                    type="button"
                                                    @click="deleteItem(item)"
                                                    class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded transition"
                                                    title="Видалити"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>

                                        {{-- Children --}}
                                        <div x-show="!collapsed && item.children && item.children.length > 0" x-collapse class="ml-6 border-l-2 border-gray-200 pl-2">
                                            <template x-for="(child, childIndex) in item.children" :key="child.id">
                                                <div x-data="{ childCollapsed: false }">
                                                    {{-- Level 1 child --}}
                                                    <div 
                                                        class="group flex items-center gap-1 py-1.5 px-2 rounded-lg transition-colors"
                                                        :class="{
                                                            'bg-blue-50 border-2 border-blue-400 border-dashed': dragOverId === child.id,
                                                            'hover:bg-gray-50': dragOverId !== child.id,
                                                            'opacity-50': !child.is_checked
                                                        }"
                                                        draggable="true"
                                                        @dragstart="handleDragStart($event, child, item.id, childIndex)"
                                                        @dragend="handleDragEnd()"
                                                        @dragover.prevent="handleDragOver($event, child)"
                                                        @dragleave="handleDragLeave($event)"
                                                        @drop.prevent="handleDrop($event, child, item.id, childIndex)"
                                                    >
                                                        <div class="flex-shrink-0 cursor-grab active:cursor-grabbing p-1 text-gray-400 hover:text-gray-600 touch-manipulation" @touchstart="handleTouchStart($event, child, item.id, childIndex)" @touchmove.prevent="handleTouchMove($event)" @touchend="handleTouchEnd($event)">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
                                                            </svg>
                                                        </div>

                                                        <button 
                                                            type="button"
                                                            @click="childCollapsed = !childCollapsed"
                                                            class="flex-shrink-0 p-1 text-gray-400 hover:text-gray-600 transition"
                                                            x-show="child.children && child.children.length > 0"
                                                        >
                                                            <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-90': !childCollapsed }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                            </svg>
                                                        </button>
                                                        <span x-show="!child.children || child.children.length === 0" class="w-6"></span>

                                                        <input 
                                                            type="checkbox"
                                                            :checked="child.is_checked"
                                                            @change="toggleItem(child)"
                                                            class="flex-shrink-0 rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500 focus:ring-offset-0"
                                                        >

                                                        <div class="flex-1 min-w-0 flex items-center gap-2" @dblclick="startEditing(child)">
                                                            <template x-if="editingId !== child.id">
                                                                <span class="text-sm truncate" :class="{ 'line-through text-gray-400': !child.is_checked }" x-text="child.title"></span>
                                                            </template>
                                                            <template x-if="editingId === child.id">
                                                                <input 
                                                                    type="text"
                                                                    x-model="editingTitle"
                                                                    @blur="saveEditing(child)"
                                                                    @keydown.enter="saveEditing(child)"
                                                                    @keydown.escape="cancelEditing()"
                                                                    class="flex-1 px-2 py-1 text-sm border border-blue-400 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                                >
                                                            </template>
                                                            <template x-if="child.level">
                                                                <span class="flex-shrink-0 inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-700" x-text="child.level"></span>
                                                            </template>
                                                        </div>

                                                        <div class="flex-shrink-0 flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                                            <button type="button" @click="startEditing(child)" class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded transition" title="Редагувати">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                            </button>
                                                            <button type="button" @click="addChild(child)" class="p-1.5 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded transition" title="Додати підрозділ">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                            </button>
                                                            <button type="button" @click="deleteItem(child)" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded transition" title="Видалити">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                            </button>
                                                        </div>
                                                    </div>

                                                    {{-- Level 2 children --}}
                                                    <div x-show="!childCollapsed && child.children && child.children.length > 0" x-collapse class="ml-6 border-l-2 border-gray-200 pl-2">
                                                        <template x-for="(grandchild, grandchildIndex) in child.children" :key="grandchild.id">
                                                            <div 
                                                                class="group flex items-center gap-1 py-1.5 px-2 rounded-lg transition-colors"
                                                                :class="{
                                                                    'bg-blue-50 border-2 border-blue-400 border-dashed': dragOverId === grandchild.id,
                                                                    'hover:bg-gray-50': dragOverId !== grandchild.id,
                                                                    'opacity-50': !grandchild.is_checked
                                                                }"
                                                                draggable="true"
                                                                @dragstart="handleDragStart($event, grandchild, child.id, grandchildIndex)"
                                                                @dragend="handleDragEnd()"
                                                                @dragover.prevent="handleDragOver($event, grandchild)"
                                                                @dragleave="handleDragLeave($event)"
                                                                @drop.prevent="handleDrop($event, grandchild, child.id, grandchildIndex)"
                                                            >
                                                                <div class="flex-shrink-0 cursor-grab active:cursor-grabbing p-1 text-gray-400 hover:text-gray-600 touch-manipulation" @touchstart="handleTouchStart($event, grandchild, child.id, grandchildIndex)" @touchmove.prevent="handleTouchMove($event)" @touchend="handleTouchEnd($event)">
                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
                                                                    </svg>
                                                                </div>
                                                                <span class="w-6"></span>

                                                                <input 
                                                                    type="checkbox"
                                                                    :checked="grandchild.is_checked"
                                                                    @change="toggleItem(grandchild)"
                                                                    class="flex-shrink-0 rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500 focus:ring-offset-0"
                                                                >

                                                                <div class="flex-1 min-w-0 flex items-center gap-2" @dblclick="startEditing(grandchild)">
                                                                    <template x-if="editingId !== grandchild.id">
                                                                        <span class="text-sm truncate" :class="{ 'line-through text-gray-400': !grandchild.is_checked }" x-text="grandchild.title"></span>
                                                                    </template>
                                                                    <template x-if="editingId === grandchild.id">
                                                                        <input 
                                                                            type="text"
                                                                            x-model="editingTitle"
                                                                            @blur="saveEditing(grandchild)"
                                                                            @keydown.enter="saveEditing(grandchild)"
                                                                            @keydown.escape="cancelEditing()"
                                                                            class="flex-1 px-2 py-1 text-sm border border-blue-400 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                                        >
                                                                    </template>
                                                                    <template x-if="grandchild.level">
                                                                        <span class="flex-shrink-0 inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-700" x-text="grandchild.level"></span>
                                                                    </template>
                                                                </div>

                                                                <div class="flex-shrink-0 flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                                                    <button type="button" @click="startEditing(grandchild)" class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded transition" title="Редагувати">
                                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                                    </button>
                                                                    <button type="button" @click="deleteItem(grandchild)" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded transition" title="Видалити">
                                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                                    </button>
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
    </div>
@endsection

@push('scripts')
    <script>
        function siteTreeEditor() {
            return {
                tree: @json($tree),
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
                draggedItem: null,
                draggedParentId: null,
                draggedIndex: null,
                dragOverId: null,
                csrfToken: null,
                touchDragItem: null,
                touchStartY: 0,

                init() {
                    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                    this.csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : null;
                },

                showMessage(text, type = 'success') {
                    this.message = text;
                    this.messageType = type;
                    setTimeout(() => { this.message = ''; }, 3000);
                },

                async fetchTree() {
                    this.loading = true;
                    try {
                        const response = await fetch('/admin/site-tree/api', {
                            headers: { 'Accept': 'application/json' }
                        });
                        const data = await response.json();
                        if (data.success) {
                            this.tree = data.tree;
                        }
                    } catch (error) {
                        this.showMessage('Помилка завантаження дерева', 'error');
                    }
                    this.loading = false;
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
                    this.$nextTick(() => {
                        const input = document.querySelector(`input[x-model="editingTitle"]`);
                        if (input) input.focus();
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
                        const data = await response.json();
                        if (data.success) {
                            item.title = data.item.title;
                            this.showMessage('Збережено');
                        }
                    } catch (error) {
                        this.showMessage('Помилка збереження', 'error');
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
                                parent_id: this.modalData.parentId
                            })
                        });
                        const data = await response.json();
                        if (data.success) {
                            await this.fetchTree();
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
                            await this.fetchTree();
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
                                'X-CSRF-TOKEN': this.csrfToken,
                                'Accept': 'application/json'
                            }
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

                expandAll() {
                    document.querySelectorAll('[x-data]').forEach(el => {
                        if (el.__x && el.__x.$data) {
                            if ('collapsed' in el.__x.$data) el.__x.$data.collapsed = false;
                            if ('childCollapsed' in el.__x.$data) el.__x.$data.childCollapsed = false;
                        }
                    });
                },

                collapseAll() {
                    document.querySelectorAll('[x-data]').forEach(el => {
                        if (el.__x && el.__x.$data) {
                            if ('collapsed' in el.__x.$data) el.__x.$data.collapsed = true;
                            if ('childCollapsed' in el.__x.$data) el.__x.$data.childCollapsed = true;
                        }
                    });
                },

                async exportTree() {
                    try {
                        const response = await fetch('/admin/site-tree/export', {
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
                            body: JSON.stringify({ tree })
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

                    // Calculate new position - drop after target
                    const newParentId = targetParentId;
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
                            await this.fetchTree();
                            this.showMessage('Переміщено');
                        } else {
                            this.showMessage(data.message || 'Помилка переміщення', 'error');
                        }
                    } catch (error) {
                        this.showMessage('Помилка переміщення', 'error');
                    }

                    this.dragOverId = null;
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
