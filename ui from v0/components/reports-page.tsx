'use client'

import { useState } from 'react'
import { makeStyles, mergeClasses, tokens, Body1, Body2, Caption1 } from '@fluentui/react-components'
import { CheckmarkCircle24Regular, Warning24Regular, Document24Regular, Clock24Regular, ChevronRight24Regular } from '@fluentui/react-icons'
import { Sidebar } from './sidebar'
import { TopNavigation } from './top-navigation'
import { Card, CardContent, CardHeader, CardTitle } from './ui/card'

const reportSummaries = [
  { label: 'Pending Approval', value: '8', tone: 'text-amber-700', badge: 'Pending' },
  { label: 'Submitted Today', value: '5', tone: 'text-slate-700', badge: 'On Track' },
  { label: 'Approved Reports', value: '24', tone: 'text-emerald-700', badge: 'Completed' },
  { label: 'Overdue Follow-Ups', value: '2', tone: 'text-red-700', badge: 'Action' },
]

const reportRows = [
  { project: 'North Tower Fit-Out', date: 'Jun 27, 2026', status: 'Pending', statusTone: 'bg-amber-50 text-amber-700', author: 'John Mitchell' },
  { project: 'West Plaza Landscaping', date: 'Jun 27, 2026', status: 'Approved', statusTone: 'bg-emerald-50 text-emerald-700', author: 'Sandra Lee' },
  { project: 'East Wing Electrical', date: 'Jun 26, 2026', status: 'Review', statusTone: 'bg-slate-100 text-slate-700', author: 'David Nunez' },
  { project: 'Parking Podium', date: 'Jun 26, 2026', status: 'Overdue', statusTone: 'bg-red-50 text-red-700', author: 'Mia Tan' },
]

const activityFeed = [
  { label: 'Report submitted', detail: 'Daily progress report created for North Tower.', time: '10m ago', icon: Document24Regular },
  { label: 'Approval requested', detail: 'Supervisor review started for West Plaza.', time: '1h ago', icon: CheckmarkCircle24Regular },
  { label: 'Report updated', detail: 'East Wing report added new safety notes.', time: '3h ago', icon: Warning24Regular },
]

const useStyles = makeStyles({
  root: {
    display: 'flex',
    height: '100vh',
    backgroundColor: '#F5F5F0',
  },
  main: {
    flex: 1,
    display: 'flex',
    flexDirection: 'column',
    overflow: 'auto',
  },
  content: {
    flex: 1,
    paddingTop: '32px',
    paddingBottom: '48px',
    paddingLeft: '32px',
    paddingRight: '32px',
    maxWidth: '1600px',
    margin: '0 auto',
    width: '100%',
  },
  header: {
    display: 'flex',
    flexDirection: 'column',
    gap: '8px',
    marginBottom: '28px',
  },
  title: {
    fontSize: '34px',
    fontWeight: 700,
    color: '#2F6B3C',
  },
  subtitle: {
    fontSize: '15px',
    color: '#666666',
    lineHeight: 1.7,
    maxWidth: '760px',
  },
  summaryGrid: {
    display: 'grid',
    gridTemplateColumns: 'repeat(4, minmax(0, 1fr))',
    gap: '20px',
    marginBottom: '32px',
    '@media (max-width: 1100px)': {
      gridTemplateColumns: 'repeat(2, minmax(0, 1fr))',
    },
    '@media (max-width: 640px)': {
      gridTemplateColumns: '1fr',
    },
  },
  summaryCard: {
    padding: '24px',
    borderRadius: '20px',
    border: '1px solid rgba(0,0,0,0.08)',
    boxShadow: '0 2px 10px rgba(0,0,0,0.06)',
    backgroundColor: '#FFFFFF',
  },
  summaryLabel: {
    fontSize: '13px',
    fontWeight: 700,
    color: '#555555',
    marginBottom: '10px',
    textTransform: 'uppercase',
    letterSpacing: '0.06em',
  },
  summaryValue: {
    fontSize: '32px',
    fontWeight: 700,
    color: '#2F6B3C',
    marginBottom: '10px',
  },
  summaryBadge: {
    display: 'inline-flex',
    alignItems: 'center',
    padding: '8px 12px',
    borderRadius: '999px',
    fontSize: '12px',
    fontWeight: 700,
    letterSpacing: '0.04em',
  },
  grid: {
    display: 'grid',
    gridTemplateColumns: '1.7fr 1fr',
    gap: '24px',
    '@media (max-width: 1024px)': {
      gridTemplateColumns: '1fr',
    },
  },
  tableWrapper: {
    overflowX: 'auto',
  },
  table: {
    width: '100%',
    borderCollapse: 'collapse',
    minWidth: '720px',
  },
  th: {
    textAlign: 'left',
    padding: '18px 20px',
    fontSize: '12px',
    fontWeight: 700,
    color: '#2F6B3C',
    textTransform: 'uppercase',
    letterSpacing: '0.08em',
    borderBottom: '1px solid rgba(148, 163, 184, 0.2)',
  },
  td: {
    padding: '18px 20px',
    fontSize: '14px',
    color: '#334155',
    borderBottom: '1px solid rgba(148, 163, 184, 0.12)',
  },
  statusPill: {
    display: 'inline-flex',
    alignItems: 'center',
    justifyContent: 'center',
    padding: '8px 12px',
    borderRadius: '999px',
    fontSize: '12px',
    fontWeight: 700,
  },
  activityCard: {
    display: 'flex',
    flexDirection: 'column',
    gap: '16px',
    padding: '24px',
    borderRadius: '20px',
    border: '1px solid rgba(0,0,0,0.08)',
    boxShadow: '0 2px 10px rgba(0,0,0,0.06)',
    backgroundColor: '#FFFFFF',
    height: '100%',
  },
  activityItem: {
    display: 'grid',
    gridTemplateColumns: '40px 1fr',
    gap: '16px',
    alignItems: 'start',
  },
  activityIcon: {
    width: '40px',
    height: '40px',
    borderRadius: '12px',
    backgroundColor: 'rgba(163, 214, 92, 0.15)',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    color: '#2F6B3C',
  },
  activityMeta: {
    display: 'flex',
    justifyContent: 'space-between',
    gap: '16px',
    flexWrap: 'wrap',
  },
  activityLabel: {
    fontSize: '15px',
    fontWeight: 700,
    color: '#111827',
  },
  activityDetail: {
    fontSize: '13px',
    color: '#475569',
    lineHeight: 1.6,
  },
  activityTime: {
    fontSize: '12px',
    color: '#667085',
    fontWeight: 600,
    whiteSpace: 'nowrap',
  },
  actionButton: {
    display: 'inline-flex',
    alignItems: 'center',
    gap: '8px',
    marginTop: '16px',
    cursor: 'pointer',
    color: '#2F6B3C',
    fontWeight: 700,
    fontSize: '13px',
    border: 'none',
    background: 'transparent',
  },
})

export function ReportsPage() {
  const styles = useStyles()
  const [sidebarOpen, setSidebarOpen] = useState(true)

  return (
    <div className={styles.root}>
      <Sidebar open={sidebarOpen} onToggle={() => setSidebarOpen(!sidebarOpen)} />
      <div className={styles.main}>
        <TopNavigation onMenuClick={() => setSidebarOpen(!sidebarOpen)} breadcrumb="Dashboard / Accomplishment Reports" />
        <div className={styles.content}>
          <div className={styles.header}>
            <div className={styles.title}>Accomplishment Reports</div>
            <Body1 className={styles.subtitle}>
              Review daily site reports, track approval status, and surface the actions that keep your project on schedule.
            </Body1>
          </div>

          <div className={styles.summaryGrid}>
            {reportSummaries.map((summary) => (
              <div key={summary.label} className={styles.summaryCard}>
                <div className={styles.summaryLabel}>{summary.label}</div>
                <div className={styles.summaryValue}>{summary.value}</div>
                <span className={styles.summaryBadge} style={{ backgroundColor: 'rgba(163,214,92,0.12)', color: '#2F6B3C' }}>
                  {summary.badge}
                </span>
              </div>
            ))}
          </div>

          <div className={styles.grid}>
            <Card className={mergeClasses(styles.activityCard)}>
              <CardHeader>
                <div className="flex items-center justify-between gap-3">
                  <CardTitle>Report Queue</CardTitle>
                  <span className="rounded-full bg-emerald-50 px-3 py-1 text-sm font-semibold text-emerald-700">Updated 15m ago</span>
                </div>
              </CardHeader>
              <CardContent className="p-0">
                <div className={styles.tableWrapper}>
                  <table className={styles.table}>
                    <thead>
                      <tr>
                        <th className={styles.th}>Project</th>
                        <th className={styles.th}>Date</th>
                        <th className={styles.th}>Status</th>
                        <th className={styles.th}>Submitted By</th>
                        <th className={styles.th} />
                      </tr>
                    </thead>
                    <tbody>
                      {reportRows.map((row) => (
                        <tr key={`${row.project}-${row.date}`}>
                          <td className={styles.td}>{row.project}</td>
                          <td className={styles.td}>{row.date}</td>
                          <td className={styles.td}>
                            <span className={styles.statusPill} style={{ backgroundColor: row.statusTone.includes('amber') ? 'rgba(251, 191, 36, 0.15)' : row.statusTone.includes('emerald') ? 'rgba(167, 243, 208, 0.25)' : row.statusTone.includes('red') ? 'rgba(254, 226, 226, 0.35)' : 'rgba(226,232,240,0.7)', color: row.statusTone.split(' ')[1] === 'text-emerald-700' ? '#047857' : row.statusTone.split(' ')[1] === 'text-red-700' ? '#B91C1C' : row.statusTone.split(' ')[1] === 'text-amber-700' ? '#92400E' : '#334155' }}>
                              {row.status}
                            </span>
                          </td>
                          <td className={styles.td}>{row.author}</td>
                          <td className={styles.td}>
                            <button className={styles.actionButton}>
                              View <ChevronRight24Regular />
                            </button>
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              </CardContent>
            </Card>

            <Card className={mergeClasses(styles.activityCard)}>
              <CardHeader>
                <div className="flex items-center justify-between gap-3">
                  <CardTitle>Recent Report Activity</CardTitle>
                  <span className="text-sm font-semibold text-slate-500">Live feed</span>
                </div>
              </CardHeader>
              <CardContent>
                <div className="space-y-4">
                  {activityFeed.map((item) => {
                    const Icon = item.icon
                    return (
                      <div key={item.label} className={styles.activityItem}>
                        <div className={styles.activityIcon}>
                          <Icon style={{ fontSize: 20 }} />
                        </div>
                        <div>
                          <div className={styles.activityMeta}>
                            <div className={styles.activityLabel}>{item.label}</div>
                            <div className={styles.activityTime}>{item.time}</div>
                          </div>
                          <Body2 className={styles.activityDetail}>{item.detail}</Body2>
                        </div>
                      </div>
                    )
                  })}
                </div>
              </CardContent>
              <button className={styles.actionButton}>
                Refresh feed <ChevronRight24Regular />
              </button>
            </Card>
          </div>
        </div>
      </div>
    </div>
  )
}
