'use client'

import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { ArrowUpRight, Boxes, AlertTriangle, Truck, PackageCheck, BarChart3, RefreshCw, ClipboardList } from 'lucide-react'

const summaryCards = [
  { label: 'Total Materials', value: '24', detail: 'Material types on site' },
  { label: 'Available', value: '16', detail: 'Ready for use' },
  { label: 'Low Stock', value: '5', detail: 'Needs replenishment' },
  { label: 'Critical', value: '2', detail: 'Immediate attention' },
  { label: 'Pending Deliveries', value: '3', detail: 'Expected today' },
]

const materials = [
  { name: 'Cement', stock: '120', unit: 'bags', usage: '76%', status: 'Healthy' },
  { name: 'Steel Rebar', stock: '38', unit: 'tons', usage: '62%', status: 'Low Stock' },
  { name: 'Pipes', stock: '12', unit: 'units', usage: '88%', status: 'Critical' },
  { name: 'Electrical Conduits', stock: '0', unit: 'bundles', usage: '100%', status: 'Out of Stock' },
]

const alerts = [
  { item: 'Pipes', remaining: '12 units', days: '2 days', priority: 'High' },
  { item: 'Electrical Conduits', remaining: '0 bundles', days: '0 days', priority: 'Critical' },
]

function statusClasses(status: string) {
  switch (status) {
    case 'Healthy':
      return 'bg-emerald-50 text-emerald-700 border-emerald-200'
    case 'Low Stock':
      return 'bg-amber-50 text-amber-700 border-amber-200'
    case 'Critical':
      return 'bg-red-50 text-red-700 border-red-200'
    case 'Out of Stock':
      return 'bg-red-50 text-red-700 border-red-200'
    default:
      return 'bg-slate-100 text-slate-700 border-slate-200'
  }
}

export function MaterialModule() {
  return (
    <div className="min-h-screen bg-[var(--bg)] p-4 md:p-6 lg:p-8">
      <div className="mx-auto max-w-7xl">
        <section className="rounded-dashboard border border-slate-200 bg-white p-6 shadow-dashboard lg:p-8">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-semibold uppercase tracking-[0.18em] text-slate-500">Material Inventory</p>
              <h1 className="text-2xl font-semibold text-slate-900">Material Monitoring</h1>
            </div>
            <div className="flex gap-2">
              <button className="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700">Refresh</button>
              <button className="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Request Restock</button>
            </div>
          </div>

          <div className="mt-6 grid gap-4 md:grid-cols-3">
            {summaryCards.map((c) => (
              <div key={c.label} className="rounded-2xl border border-slate-200 bg-white p-4">
                <p className="text-xs font-semibold uppercase text-slate-500">{c.label}</p>
                <p className="mt-2 text-2xl font-semibold text-slate-900">{c.value}</p>
                <p className="mt-1 text-sm text-slate-600">{c.detail}</p>
              </div>
            ))}
          </div>
        </section>
      </div>
    </div>
  )
}
