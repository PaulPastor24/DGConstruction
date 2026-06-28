'use client'

import { Badge } from '@fluentui/react-components'
import { CalendarDays, Fingerprint, UserCheck2, UserX, ShieldCheck, ArrowUpRight, RefreshCw, FileDown, Clock3, Users, AlertTriangle, ClipboardList } from 'lucide-react'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'

const attendanceRows = [
  { name: 'A. Raza', trade: 'Steel Fixing', timeIn: '06:15', timeOut: '17:30', status: 'Present', biometric: 'Verified', remarks: 'On schedule' },
  { name: 'M. Santos', trade: 'Electrical', timeIn: '06:40', timeOut: '17:20', status: 'Half Day', biometric: 'Verified', remarks: 'Early departure' },
  { name: 'J. Miller', trade: 'Masonry', timeIn: '-', timeOut: '-', status: 'Absent', biometric: 'Not Verified', remarks: 'No scan recorded' },
  { name: 'O. Hassan', trade: 'Plumbing', timeIn: '06:10', timeOut: '17:00', status: 'Leave', biometric: 'Not Verified', remarks: 'Approved leave' },
]

const insights = [
  { label: 'Attendance rate', value: '91%', tone: 'text-emerald-600' },
  { label: 'Late arrivals', value: '4', tone: 'text-amber-600' },
  { label: 'Missing attendance', value: '2', tone: 'text-red-600' },
  { label: 'No biometric verification', value: '3', tone: 'text-amber-600' },
]

const summaryCards = [
  { label: 'Total Workers Assigned', value: '48', detail: 'Across current project' },
  { label: 'Present Today', value: '39', detail: 'On site now' },
  { label: 'Absent', value: '4', detail: 'No attendance scan' },
  { label: 'On Leave', value: '2', detail: 'Approved absence' },
  { label: 'Half Day', value: '3', detail: 'Partial attendance' },
  { label: 'Biometric Verified', value: '42', detail: 'Verified by mobile app' },
]

function statusClasses(status: string) {
  switch (status) {
    case 'Present':
      return 'bg-emerald-50 text-emerald-700 border-emerald-200'
    case 'Half Day':
      return 'bg-amber-50 text-amber-700 border-amber-200'
    case 'Absent':
      return 'bg-red-50 text-red-700 border-red-200'
    case 'Leave':
      return 'bg-slate-100 text-slate-700 border-slate-200'
    default:
      return 'bg-slate-100 text-slate-700 border-slate-200'
  }
}

function verificationClasses(value: string) {
  return value === 'Verified'
    ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
    : 'bg-amber-50 text-amber-700 border-amber-200'
}

export function AttendanceModule() {
  return (
    <div className="min-h-screen bg-[var(--bg)] p-4 md:p-6 lg:p-8">
      <div className="mx-auto flex max-w-7xl flex-col gap-6">
        <section className="rounded-dashboard border border-slate-200 bg-white p-6 shadow-dashboard lg:p-8">
          <div className="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
            <div className="space-y-4">
              <div className="inline-flex items-center gap-2 rounded-full border border-[rgba(163,214,92,0.3)] bg-[rgba(163,214,92,0.12)] px-3 py-1 text-sm font-semibold text-slate-700">
                <CalendarDays size={16} className="text-[#82b94a]" />
                Today’s Attendance
              </div>
              <div>
                <h1 className="text-2xl font-semibold tracking-tight text-slate-900 md:text-3xl">Today’s Attendance</h1>
                <p className="mt-2 max-w-2xl text-sm text-slate-600 md:text-base">
                  Monitor workforce attendance captured through the mobile biometric application and review site readiness in real time.
                </p>
              </div>
            </div>
            <div className="flex flex-wrap gap-2">
              <button className="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:-translate-y-0.5 hover:bg-slate-50">
                <RefreshCw size={16} /> Refresh
              </button>
              <button className="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:-translate-y-0.5 hover:bg-slate-700">
                <FileDown size={16} /> Export
              </button>
            </div>
          </div>

          <div className="mt-8 grid gap-4 xl:grid-cols-[1.3fr_0.7fr]">
            <div className="rounded-dashboard border border-slate-200 bg-[linear-gradient(135deg,#ffffff_0%,#f8faf8_100%)] p-6">
              <div className="flex flex-wrap items-center justify-between gap-3">
                <div>
                  <p className="text-sm font-semibold uppercase tracking-[0.18em] text-slate-500">Assigned Project</p>
                  <p className="mt-1 text-lg font-semibold text-slate-900">North Tower Fit-Out</p>
                </div>
                <div className="rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-sm font-semibold text-emerald-700">
                  Active Site
                </div>
              </div>
              <div className="mt-6 grid gap-4 md:grid-cols-3">
                <div className="rounded-2xl border border-slate-200 bg-white p-4">
                  <p className="text-sm text-slate-500">Attendance Rate</p>
                  <p className="mt-1 text-3xl font-semibold text-slate-900">91%</p>
                </div>
                <div className="rounded-2xl border border-slate-200 bg-white p-4">
                  <p className="text-sm text-slate-500">Present Workers</p>
                  <p className="mt-1 text-3xl font-semibold text-slate-900">39</p>
                </div>
                <div className="rounded-2xl border border-slate-200 bg-white p-4">
                  <p className="text-sm text-slate-500">Absent Workers</p>
                  <p className="mt-1 text-3xl font-semibold text-slate-900">4</p>
                </div>
              </div>
            </div>
            <div className="rounded-dashboard border border-slate-200 bg-[linear-gradient(135deg,#fcfdfc_0%,#f4f8f6_100%)] p-6">
              <div className="flex items-center gap-2 text-sm font-semibold uppercase tracking-[0.18em] text-slate-500">
                <ShieldCheck size={16} className="text-[#82b94a]" /> Biometric Verification
              </div>
              <div className="mt-4 flex items-end gap-3">
                <div className="text-5xl font-semibold text-slate-900">84%</div>
                <div className="rounded-full bg-emerald-50 px-3 py-1 text-sm font-semibold text-emerald-700">Verified</div>
              </div>
              <div className="mt-4 h-2 overflow-hidden rounded-full bg-slate-200">
                <div className="h-full w-[84%] rounded-full bg-gradient-to-r from-[#82b94a] to-[#A3D65C]" />
              </div>
              <p className="mt-3 text-sm text-slate-600">Attendance records from the mobile app are being validated for the current shift.</p>
            </div>
          </div>
        </section>

        <section className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
          {summaryCards.map((card) => (
            <Card key={card.label} className="border-l-4 border-l-[#A3D65C] transition hover:-translate-y-1 hover:shadow-xl">
              <CardContent className="p-5">
                <p className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">{card.label}</p>
                <p className="mt-2 text-3xl font-semibold text-slate-900">{card.value}</p>
                <p className="mt-1 text-sm text-slate-600">{card.detail}</p>
              </CardContent>
            </Card>
          ))}
        </section>

        <div className="grid gap-6 xl:grid-cols-[1.4fr_0.6fr]">
          <Card className="overflow-hidden">
            <CardHeader>
              <div className="flex items-center justify-between gap-3">
                <CardTitle>Worker Attendance List</CardTitle>
                <div className="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-sm font-semibold text-slate-600">Today</div>
              </div>
            </CardHeader>
            <CardContent className="p-0">
              <div className="overflow-x-auto">
                <table className="min-w-full divide-y divide-slate-200 text-sm">
                  <thead className="bg-slate-50">
                    <tr>
                      <th className="px-4 py-3 text-left font-semibold text-slate-600">Worker</th>
                      <th className="px-4 py-3 text-left font-semibold text-slate-600">Trade</th>
                      <th className="px-4 py-3 text-left font-semibold text-slate-600">Time In</th>
                      <th className="px-4 py-3 text-left font-semibold text-slate-600">Time Out</th>
                      <th className="px-4 py-3 text-left font-semibold text-slate-600">Status</th>
                      <th className="px-4 py-3 text-left font-semibold text-slate-600">Verification</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-slate-200 bg-white">
                    {attendanceRows.map((row) => (
                      <tr key={row.name} className="hover:bg-slate-50">
                        <td className="px-4 py-3">
                          <div className="flex items-center gap-3">
                            <div className="flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-sm font-semibold text-slate-700">{row.name.split(' ')[1]?.[0] ?? 'U'}</div>
                            <div>
                              <div className="font-semibold text-slate-900">{row.name}</div>
                              <div className="text-xs text-slate-500">{row.remarks}</div>
                            </div>
                          </div>
                        </td>
                        <td className="px-4 py-3 text-slate-600">{row.trade}</td>
                        <td className="px-4 py-3 text-slate-600">{row.timeIn}</td>
                        <td className="px-4 py-3 text-slate-600">{row.timeOut}</td>
                        <td className="px-4 py-3"><span className={`rounded-full border px-2.5 py-1 text-xs font-semibold ${statusClasses(row.status)}`}>{row.status}</span></td>
                        <td className="px-4 py-3"><span className={`rounded-full border px-2.5 py-1 text-xs font-semibold ${verificationClasses(row.biometric)}`}>{row.biometric}</span></td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </CardContent>
          </Card>

          <div className="space-y-6">
            <Card>
              <CardHeader>
                <CardTitle>Attendance Insights</CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                {insights.map((item) => (
                  <div key={item.label} className="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <span className="text-sm text-slate-600">{item.label}</span>
                    <span className={`text-sm font-semibold ${item.tone}`}>{item.value}</span>
                  </div>
                ))}
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle>Quick Actions</CardTitle>
              </CardHeader>
              <CardContent className="space-y-3">
                <button className="flex w-full items-center justify-between rounded-2xl border border-slate-200 bg-white px-4 py-3 text-left text-sm font-semibold text-slate-700 transition hover:-translate-y-0.5 hover:bg-slate-50">
                  <span className="flex items-center gap-2"><ClipboardList size={16} /> View Attendance Details</span>
                  <ArrowUpRight size={16} />
                </button>
                <button className="flex w-full items-center justify-between rounded-2xl border border-slate-200 bg-white px-4 py-3 text-left text-sm font-semibold text-slate-700 transition hover:-translate-y-0.5 hover:bg-slate-50">
                  <span className="flex items-center gap-2"><RefreshCw size={16} /> Refresh Attendance</span>
                  <ArrowUpRight size={16} />
                </button>
                <button className="flex w-full items-center justify-between rounded-2xl border border-slate-200 bg-white px-4 py-3 text-left text-sm font-semibold text-slate-700 transition hover:-translate-y-0.5 hover:bg-slate-50">
                  <span className="flex items-center gap-2"><FileDown size={16} /> Export Attendance</span>
                  <ArrowUpRight size={16} />
                </button>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </div>
  )
}
