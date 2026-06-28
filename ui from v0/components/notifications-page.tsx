'use client'

import { useMemo, useState } from 'react'
import Link from 'next/link'
import { BellRing, CalendarClock, CheckCheck, ChevronRight, Clock3, FileText, HardHat, Search, ShieldAlert, TriangleAlert, Boxes, ArrowRight } from 'lucide-react'
import { Sidebar } from './sidebar'
import { TopNavigation } from './top-navigation'

type NotificationType = 'Attendance' | 'Construction Phases' | 'Materials' | 'Reports' | 'Project Timeline' | 'System'
type NotificationPriority = 'High' | 'Medium' | 'Low' | 'Critical'
type NotificationStatus = 'Unread' | 'Read'

type NotificationItem = {
  id: number
  title: string
  description: string
  project: string
  timestamp: string
  type: NotificationType
  priority: NotificationPriority
  status: NotificationStatus
  module: string
  action: string
  fullMessage: string
}

const initialNotifications: NotificationItem[] = [
  {
    id: 1,
    title: 'Attendance follow-up needed',
    description: '2 workers have not completed biometric attendance for today.',
    project: 'North Tower Fit-Out',
    timestamp: '15 min ago',
    type: 'Attendance',
    priority: 'High',
    status: 'Unread',
    module: 'attendance',
    action: 'Open Attendance Module',
    fullMessage: 'Two workers are still pending biometric attendance completion. This is blocking the daily workforce log and should be resolved before the site handoff meeting.',
  },
  {
    id: 2,
    title: 'Critical material threshold reached',
    description: 'Steel bars have reached the critical stock threshold for this phase.',
    project: 'Riverfront Residence',
    timestamp: '1 hour ago',
    type: 'Materials',
    priority: 'High',
    status: 'Unread',
    module: 'material',
    action: 'Open Material Monitoring',
    fullMessage: 'The current steel bar allocation is below the minimum safe threshold for the active phase. A reorder or reschedule should be reviewed immediately.',
  },
  {
    id: 3,
    title: 'Report approved by engineer',
    description: 'Your accomplishment report has been approved and moved to the archive.',
    project: 'Harbor Logistics Hub',
    timestamp: '3 hours ago',
    type: 'Reports',
    priority: 'Medium',
    status: 'Read',
    module: 'reports',
    action: 'Open Reports',
    fullMessage: 'The latest accomplishment report has been approved by the engineering team. No further action is required before the next reporting window.',
  },
  {
    id: 4,
    title: 'Construction phase behind target',
    description: 'Structural Works is now 2 days behind the planned completion window.',
    project: 'North Tower Fit-Out',
    timestamp: 'Today • 08:20',
    type: 'Construction Phases',
    priority: 'Medium',
    status: 'Unread',
    module: 'phases',
    action: 'Open Construction Phases',
    fullMessage: 'Structural Works has slipped behind the expected schedule and now requires revised sequencing to recover time before the next milestone review.',
  },
  {
    id: 5,
    title: 'Timeline milestone due soon',
    description: 'A milestone review is scheduled for tomorrow morning at 09:00.',
    project: 'Riverfront Residence',
    timestamp: 'Today • 06:45',
    type: 'Project Timeline',
    priority: 'Low',
    status: 'Read',
    module: 'timeline',
    action: 'Open Project Timeline',
    fullMessage: 'The next milestone review is approaching and should be prepared with the latest progress notes and dependencies before the morning review.',
  },
]

const typeOptions: Array<'All' | NotificationType> = ['All', 'Unread', 'Attendance', 'Construction Phases', 'Materials', 'Reports', 'Project Timeline', 'System']
const statusOptions: Array<'All' | NotificationStatus> = ['All', 'Unread', 'Read']
const dateOptions = ['All', 'Today', 'This week']

export function NotificationsPage() {
  const [notifications, setNotifications] = useState(initialNotifications)
  const [selectedId, setSelectedId] = useState<number | null>(initialNotifications[0]?.id ?? null)
  const [search, setSearch] = useState('')
  const [typeFilter, setTypeFilter] = useState<'All' | NotificationType | 'Unread'>('All')
  const [statusFilter, setStatusFilter] = useState<'All' | NotificationStatus>('All')
  const [dateFilter, setDateFilter] = useState('All')
  const [sidebarOpen, setSidebarOpen] = useState(true)

  const unreadCount = notifications.filter((item) => item.status === 'Unread').length

  const filteredNotifications = useMemo(() => {
    return notifications.filter((item) => {
      const matchesSearch = `${item.title} ${item.description} ${item.project}`.toLowerCase().includes(search.toLowerCase())
      const matchesType = typeFilter === 'All' || (typeFilter === 'Unread' ? item.status === 'Unread' : item.type === typeFilter)
      const matchesStatus = statusFilter === 'All' || item.status === statusFilter
      const matchesDate = dateFilter === 'All' || (dateFilter === 'Today' && item.timestamp.includes('Today')) || (dateFilter === 'This week' && !item.timestamp.includes('Today') && !item.timestamp.includes('ago'))
      return matchesSearch && matchesType && matchesStatus && matchesDate
    })
  }, [notifications, search, typeFilter, statusFilter, dateFilter])

  const selectedNotification = filteredNotifications.find((item) => item.id === selectedId) ?? filteredNotifications[0] ?? null

  const markAsRead = (id: number) => {
    setNotifications((current) => current.map((item) => item.id === id ? { ...item, status: 'Read' } : item))
  }

  const markAllAsRead = () => {
    setNotifications((current) => current.map((item) => ({ ...item, status: 'Read' })))
  }

  const openDetails = (id: number) => {
    setSelectedId(id)
    markAsRead(id)
  }

  const dismissNotification = (id: number) => {
    setNotifications((current) => current.filter((item) => item.id !== id))
    setSelectedId((current) => (current === id ? null : current))
  }

  return (
    <div className="min-h-screen bg-[#f5f5f0]">
      <div className="flex min-h-screen">
        <Sidebar open={sidebarOpen} onToggle={() => setSidebarOpen(!sidebarOpen)} />
        <div className="flex-1 flex flex-col overflow-hidden">
          <TopNavigation onMenuClick={() => setSidebarOpen(!sidebarOpen)} breadcrumb="Dashboard / Notifications" />
          <main className="flex-1 overflow-auto px-4 py-5 sm:px-6 lg:px-8 xl:px-10">
            <div className="mx-auto flex max-w-7xl flex-col gap-4">
              <section className="rounded-[20px] border border-[rgba(15,23,42,0.08)] bg-white shadow-[0_10px_24px_rgba(9,96,86,0.05)]">
                <div className="border-l-4 border-[#A3D65C] p-5 sm:p-6 lg:p-7">
                  <div className="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                      <div className="text-[11px] font-semibold uppercase tracking-[0.18em] text-[#6b7280]">Notification Center</div>
                      <h1 className="mt-2 text-2xl font-semibold text-[#2F6B3C] sm:text-3xl">What needs your attention right now?</h1>
                      <p className="mt-2 max-w-2xl text-sm text-[#6b7280] sm:text-[15px]">
                        Review unread updates across attendance, materials, reports, phases, and project timeline items from a single workspace.
                      </p>
                    </div>
                    <div className="flex flex-col gap-3 rounded-[16px] border border-[rgba(15,23,42,0.08)] bg-[#f8fcf5] p-4 sm:min-w-[220px]">
                      <div className="flex items-center gap-2 text-sm font-semibold text-[#2F6B3C]">
                        <BellRing className="h-4 w-4" />
                        <span>{unreadCount} unread notifications</span>
                      </div>
                      <div className="text-sm text-[#6b7280]">{new Date().toLocaleDateString('en-US', { weekday: 'long', month: 'short', day: 'numeric' })}</div>
                      <button onClick={markAllAsRead} className="inline-flex items-center justify-center rounded-[12px] bg-[#2F6B3C] px-4 py-2 text-sm font-semibold text-white shadow-[0_8px_18px_rgba(9,96,86,0.16)] transition hover:-translate-y-[1px] hover:bg-[#245a32]">
                        <CheckCheck className="mr-2 h-4 w-4" />
                        Mark all as read
                      </button>
                    </div>
                  </div>
                </div>
              </section>

              <section className="rounded-[20px] border border-[rgba(15,23,42,0.08)] bg-white p-4 shadow-[0_10px_24px_rgba(9,96,86,0.05)] sm:p-5">
                <div className="flex flex-col gap-3">
                  <label className="flex flex-col gap-2">
                    <span className="text-[11px] font-semibold uppercase tracking-[0.18em] text-[#6b7280]">Quick search</span>
                    <div className="flex items-center rounded-[12px] border border-[rgba(15,23,42,0.08)] bg-[#fcfdfc] px-3 py-2">
                      <Search className="mr-2 h-4 w-4 text-[#6b7280]" />
                      <input value={search} onChange={(e) => setSearch(e.target.value)} placeholder="Search notifications" className="w-full bg-transparent text-sm outline-none" />
                    </div>
                  </label>
                  <div className="flex flex-wrap gap-2">
                    {typeOptions.map((option) => (
                      <button key={option} onClick={() => setTypeFilter(option)} className={`rounded-full px-3 py-2 text-sm font-semibold transition ${typeFilter === option ? 'bg-[#2F6B3C] text-white' : 'bg-[#f8fcf5] text-[#2F6B3C]'}`}>
                        {option}
                      </button>
                    ))}
                  </div>
                  <div className="flex flex-wrap gap-2">
                    {statusOptions.map((option) => (
                      <button key={option} onClick={() => setStatusFilter(option)} className={`rounded-full px-3 py-2 text-sm font-semibold transition ${statusFilter === option ? 'bg-[#2F6B3C] text-white' : 'bg-[#f8fcf5] text-[#2F6B3C]'}`}>
                        {option}
                      </button>
                    ))}
                    {dateOptions.map((option) => (
                      <button key={option} onClick={() => setDateFilter(option)} className={`rounded-full px-3 py-2 text-sm font-semibold transition ${dateFilter === option ? 'bg-[#2F6B3C] text-white' : 'bg-[#f8fcf5] text-[#2F6B3C]'}`}>
                        {option}
                      </button>
                    ))}
                  </div>
                </div>
              </section>

              <div className="grid gap-4 xl:grid-cols-[1.1fr_0.9fr]">
                <section className="space-y-3">
                  {filteredNotifications.length === 0 ? (
                    <div className="rounded-[20px] border border-dashed border-[rgba(15,23,42,0.12)] bg-[#fbfdf9] p-8 text-center shadow-[0_8px_18px_rgba(9,96,86,0.04)]">
                      <div className="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-[#eef7e5] text-[#2F6B3C]">
                        <BellRing className="h-6 w-6" />
                      </div>
                      <h2 className="text-lg font-semibold text-[#2F6B3C]">No new notifications.</h2>
                      <p className="mt-2 text-sm text-[#6b7280]">You are all caught up. New activity will appear here as it arrives.</p>
                    </div>
                  ) : (
                    filteredNotifications.map((item) => (
                      <article key={item.id} className={`rounded-[18px] border p-4 shadow-[0_8px_18px_rgba(9,96,86,0.04)] transition ${item.status === 'Unread' ? 'border-[#A3D65C]/60 bg-[#f8fcf5]' : 'border-[rgba(15,23,42,0.08)] bg-white'}`}>
                        <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                          <div className="flex gap-3">
                            <div className="mt-1 flex h-10 w-10 shrink-0 items-center justify-center rounded-[12px] bg-[#eef7e5] text-[#2F6B3C]">
                              {item.type === 'Attendance' && <ShieldAlert className="h-5 w-5" />}
                              {item.type === 'Construction Phases' && <HardHat className="h-5 w-5" />}
                              {item.type === 'Materials' && <Boxes className="h-5 w-5" />}
                              {item.type === 'Reports' && <FileText className="h-5 w-5" />}
                              {item.type === 'Project Timeline' && <CalendarClock className="h-5 w-5" />}
                              {item.type === 'System' && <BellRing className="h-5 w-5" />}
                            </div>
                            <div>
                              <div className="flex flex-wrap items-center gap-2">
                                <h3 className="text-base font-semibold text-[#111827]">{item.title}</h3>
                                {item.status === 'Unread' && <span className="rounded-full bg-[#A3D65C]/20 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-[#2F6B3C]">Unread</span>}
                              </div>
                              <p className="mt-1 text-sm leading-6 text-[#6b7280]">{item.description}</p>
                              <div className="mt-2 flex flex-wrap gap-2 text-xs text-[#6b7280]">
                                <span className="rounded-full bg-[#f3f7f2] px-2.5 py-1">{item.project}</span>
                                <span className="rounded-full bg-[#f3f7f2] px-2.5 py-1">{item.timestamp}</span>
                              </div>
                            </div>
                          </div>
                          <div className="flex flex-wrap items-center gap-2 sm:justify-end">
                            <span className={`rounded-full px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] ${item.priority === 'Critical' ? 'bg-[#fef2f2] text-[#b91c1c]' : item.priority === 'High' ? 'bg-[#fefce8] text-[#a16207]' : item.priority === 'Medium' ? 'bg-[#fefce8] text-[#a16207]' : 'bg-[#f0fdf4] text-[#166534]'}`}>{item.priority}</span>
                            <button onClick={() => openDetails(item.id)} className="inline-flex items-center rounded-[10px] border border-[rgba(15,23,42,0.08)] bg-white px-3 py-2 text-sm font-semibold text-[#2F6B3C] transition hover:bg-[#f8fcf5]">
                              View details
                              <ChevronRight className="ml-1 h-4 w-4" />
                            </button>
                          </div>
                        </div>
                      </article>
                    ))
                  )}
                </section>

                <aside className="rounded-[20px] border border-[rgba(15,23,42,0.08)] bg-[#fcfdfc] p-4 shadow-[0_10px_24px_rgba(9,96,86,0.05)] sm:p-5">
                  {selectedNotification ? (
                    <>
                      <div className="flex items-start justify-between gap-3">
                        <div>
                          <div className="text-[11px] font-semibold uppercase tracking-[0.18em] text-[#6b7280]">Notification details</div>
                          <h2 className="mt-1 text-xl font-semibold text-[#2F6B3C]">{selectedNotification.title}</h2>
                        </div>
                        <div className="rounded-full bg-[#eef7e5] p-2 text-[#2F6B3C]">
                          {selectedNotification.type === 'Attendance' && <ShieldAlert className="h-5 w-5" />}
                          {selectedNotification.type === 'Construction Phases' && <HardHat className="h-5 w-5" />}
                          {selectedNotification.type === 'Materials' && <Boxes className="h-5 w-5" />}
                          {selectedNotification.type === 'Reports' && <FileText className="h-5 w-5" />}
                          {selectedNotification.type === 'Project Timeline' && <CalendarClock className="h-5 w-5" />}
                          {selectedNotification.type === 'System' && <BellRing className="h-5 w-5" />}
                        </div>
                      </div>

                      <div className="mt-4 space-y-3 rounded-[16px] border border-[rgba(15,23,42,0.08)] bg-white p-4">
                        <div className="text-sm leading-7 text-[#6b7280]">{selectedNotification.fullMessage}</div>
                        <div className="grid gap-3 text-sm text-[#334155] sm:grid-cols-2">
                          <div className="rounded-[12px] bg-[#f8fcf5] p-3">
                            <div className="text-[11px] font-semibold uppercase tracking-[0.14em] text-[#6b7280]">Related project</div>
                            <div className="mt-1 font-semibold text-[#111827]">{selectedNotification.project}</div>
                          </div>
                          <div className="rounded-[12px] bg-[#f8fcf5] p-3">
                            <div className="text-[11px] font-semibold uppercase tracking-[0.14em] text-[#6b7280]">Source module</div>
                            <div className="mt-1 font-semibold text-[#111827]">{selectedNotification.type}</div>
                          </div>
                        </div>
                        <div className="flex items-center gap-2 text-sm text-[#6b7280]">
                          <Clock3 className="h-4 w-4" />
                          <span>{selectedNotification.timestamp}</span>
                        </div>
                      </div>

                      <div className="mt-4 flex flex-col gap-2 sm:flex-row">
                        <button onClick={() => markAsRead(selectedNotification.id)} className="inline-flex items-center justify-center rounded-[12px] bg-[#2F6B3C] px-4 py-2 text-sm font-semibold text-white transition hover:-translate-y-[1px] hover:bg-[#245a32]">
                          <CheckCheck className="mr-2 h-4 w-4" />
                          Mark as read
                        </button>
                        <Link href={`/${selectedNotification.module}`} className="inline-flex items-center justify-center rounded-[12px] border border-[rgba(15,23,42,0.08)] bg-white px-4 py-2 text-sm font-semibold text-[#2F6B3C] transition hover:bg-[#f8fcf5]">
                          <ArrowRight className="mr-2 h-4 w-4" />
                          Go to related module
                        </Link>
                        <button onClick={() => dismissNotification(selectedNotification.id)} className="inline-flex items-center justify-center rounded-[12px] border border-[rgba(15,23,42,0.08)] bg-white px-4 py-2 text-sm font-semibold text-[#6b7280] transition hover:bg-[#f8fcf5]">
                          Dismiss
                        </button>
                      </div>
                    </>
                  ) : (
                    <div className="rounded-[16px] border border-dashed border-[rgba(15,23,42,0.12)] bg-white p-6 text-center">
                      <TriangleAlert className="mx-auto mb-2 h-8 w-8 text-[#A3D65C]" />
                      <div className="text-sm font-semibold text-[#2F6B3C]">Select a notification</div>
                      <div className="mt-1 text-sm text-[#6b7280]">Open an item from the list to view its full details.</div>
                    </div>
                  )}
                </aside>
              </div>
            </div>
          </main>
        </div>
      </div>
    </div>
  )
}
