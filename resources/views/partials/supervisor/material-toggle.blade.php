<div class="material-toggle-bar flex flex-col sm:flex-row flex-wrap items-center gap-1 p-2 sm:p-3 border-b border-gray-200 bg-gray-50/60">
    <div class="inline-flex flex-wrap items-center gap-1 p-1 w-full sm:w-auto bg-white border border-gray-200 rounded-card shadow-sm">
        <button type="button" @click="activeTab = 'inventory'"
            :class="activeTab === 'inventory' ? 'bg-brand-dark text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100'"
            class="flex-1 sm:flex-none inline-flex items-center justify-center gap-1 sm:gap-1.5 px-2 sm:px-5 py-1.5 sm:py-2.5 text-xs sm:text-sm font-bold rounded-btn transition whitespace-nowrap">
            <i class="bi bi-box-seam"></i> <span class="hidden xs:inline">Inventory</span><span class="xs:hidden">Stock</span>
        </button>
        <button type="button" @click="activeTab = 'requests'"
            :class="activeTab === 'requests' ? 'bg-brand-dark text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100'"
            class="flex-1 sm:flex-none inline-flex items-center justify-center gap-1 sm:gap-1.5 px-2 sm:px-5 py-1.5 sm:py-2.5 text-xs sm:text-sm font-bold rounded-btn transition whitespace-nowrap">
            <i class="bi bi-cart-plus"></i> <span class="hidden xs:inline">Requests</span><span class="xs:hidden">Request Logs</span>
        </button>
        <button type="button" @click="activeTab = 'usage'"
            :class="activeTab === 'usage' ? 'bg-brand-dark text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100'"
            class="flex-1 sm:flex-none inline-flex items-center justify-center gap-1 sm:gap-1.5 px-2 sm:px-5 py-1.5 sm:py-2.5 text-xs sm:text-sm font-bold rounded-btn transition whitespace-nowrap">
            <i class="bi bi-clock-history"></i> <span class="hidden xs:inline">Usage</span><span class="xs:hidden">Record Logs</span>
        </button>
    </div>

    <div class="grid grid-cols-2 gap-1.5 sm:flex sm:flex-wrap sm:items-center sm:justify-end sm:gap-2 sm:ml-auto w-full sm:w-auto mt-1 sm:mt-0">
        <button type="button" @click="openRequestModal = true" class="w-full sm:w-auto inline-flex items-center justify-center gap-1 px-2 py-1.5 text-xs sm:text-sm font-bold text-white bg-[#2a4028] rounded-btn shadow-saas hover:bg-green-900 transition hover:scale-[1.01] active:scale-[0.99] whitespace-nowrap">
            <i class="bi bi-cart-plus"></i> Request
        </button>
        <button type="button" @click="openUsageModal = true" class="w-full sm:w-auto inline-flex items-center justify-center gap-1 px-2 py-1.5 text-xs sm:text-sm font-bold text-white bg-brand-dark rounded-btn shadow-saas hover:bg-green-900 transition hover:scale-[1.01] active:scale-[0.99] whitespace-nowrap">
            <i class="bi bi-plus-lg"></i> Record
        </button>
    </div>
</div>
