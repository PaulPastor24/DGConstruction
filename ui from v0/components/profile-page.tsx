'use client'

import { useState } from 'react'
import { makeStyles, mergeClasses, tokens, Body1, Body2, Caption1, Title3 } from '@fluentui/react-components'
import { ShieldCheckmark24Regular, Person24Regular, ServiceBell24Regular, Calendar24Regular, People24Regular, Document24Regular } from '@fluentui/react-icons'
import { Sidebar } from './sidebar'
import { TopNavigation } from './top-navigation'
import { Card, CardContent, CardHeader, CardTitle } from './ui/card'

const statCards = [
  { label: 'Projects Overseen', value: '3' },
  { label: 'Avg. Completion', value: '72%' },
  { label: 'Pending Approvals', value: '5' },
  { label: 'Reports Submitted', value: '128' },
]

const profileDetails = [
  { label: 'Email', value: 'john.mitchell@dgconstruction.com' },
  { label: 'Phone', value: '+1 (312) 555-0198' },
  { label: 'Location', value: 'Chicago, IL' },
  { label: 'Assigned Project', value: 'North Tower Fit-Out' },
]

const accountSummary = [
  { label: 'Employee ID', value: 'EMP-1048' },
  { label: 'Position', value: 'Site Supervisor' },
  { label: 'Employment Status', value: 'Active' },
]

const notifications = [
  { label: 'Daily report reminders', active: true },
  { label: 'Approval status updates', active: true },
  { label: 'Safety alerts', active: false },
  { label: 'Schedule change notices', active: true },
]

const activityLog = [
  { label: 'Password updated', time: 'Today • 9:12 AM', icon: ShieldCheckmark24Regular },
  { label: 'Profile reviewed', time: 'Jun 26 • 4:45 PM', icon: Person24Regular },
  { label: 'Notification settings saved', time: 'Jun 25 • 11:20 AM', icon: ServiceBell24Regular },
  { label: 'New assignment added', time: 'Jun 24 • 2:30 PM', icon: Calendar24Regular },
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
    maxWidth: '760px',
    lineHeight: 1.7,
  },
  layout: {
    display: 'grid',
    gridTemplateColumns: '1.1fr 0.9fr',
    gap: '24px',
    '@media (max-width: 1024px)': {
      gridTemplateColumns: '1fr',
    },
  },
  profileCard: {
    display: 'grid',
    gap: '24px',
    padding: '24px',
    borderRadius: '20px',
    border: '1px solid rgba(0,0,0,0.08)',
    boxShadow: '0 2px 10px rgba(0,0,0,0.06)',
    backgroundColor: '#FFFFFF',
  },
  profileOverview: {
    display: 'flex',
    gap: '24px',
    alignItems: 'center',
    flexWrap: 'wrap',
  },
  avatar: {
    width: '98px',
    height: '98px',
    borderRadius: '24px',
    backgroundColor: '#E6F4DD',
    color: '#2F6B3C',
    display: 'grid',
    placeItems: 'center',
    fontSize: '36px',
    fontWeight: 800,
  },
  profileName: {
    fontSize: '24px',
    fontWeight: 700,
    color: '#111827',
  },
  profileMeta: {
    display: 'grid',
    gap: '4px',
  },
  profileRole: {
    fontSize: '15px',
    color: '#475569',
    fontWeight: 600,
  },
  metaItem: {
    fontSize: '14px',
    color: '#475569',
  },
  statGrid: {
    display: 'grid',
    gridTemplateColumns: 'repeat(2, minmax(0, 1fr))',
    gap: '16px',
    '@media (max-width: 640px)': {
      gridTemplateColumns: '1fr',
    },
  },
  statCard: {
    padding: '20px',
    borderRadius: '18px',
    border: '1px solid rgba(0,0,0,0.08)',
    backgroundColor: '#F8FAF5',
  },
  statLabel: {
    fontSize: '12px',
    fontWeight: 700,
    color: '#6B7280',
    textTransform: 'uppercase',
    letterSpacing: '0.08em',
    marginBottom: '10px',
  },
  statValue: {
    fontSize: '28px',
    fontWeight: 700,
    color: '#2F6B3C',
  },
  detailRow: {
    display: 'grid',
    gridTemplateColumns: '140px 1fr',
    gap: '12px',
    alignItems: 'start',
    padding: '14px 0',
    borderBottom: '1px solid rgba(148, 163, 184, 0.15)',
    '&:last-child': {
      borderBottom: 'none',
    },
  },
  detailLabel: {
    fontSize: '13px',
    fontWeight: 700,
    color: '#475569',
  },
  detailValue: {
    fontSize: '14px',
    color: '#334155',
    lineHeight: 1.7,
  },
  preferences: {
    display: 'grid',
    gap: '12px',
  },
  preferenceItem: {
    display: 'flex',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: '16px',
    borderRadius: '16px',
    border: '1px solid rgba(0,0,0,0.08)',
    backgroundColor: '#FFFFFF',
  },
  toggle: {
    width: '42px',
    height: '24px',
    borderRadius: '999px',
    backgroundColor: '#E5E7EB',
    position: 'relative',
    flexShrink: 0,
  },
  toggleDot: {
    position: 'absolute',
    top: '2px',
    left: '2px',
    width: '20px',
    height: '20px',
    borderRadius: '999px',
    backgroundColor: '#FFFFFF',
    boxShadow: '0 1px 4px rgba(15, 23, 42, 0.12)',
    transition: 'left 0.2s ease',
  },
  enabledDot: {
    left: 'calc(100% - 22px)',
  },
  activityList: {
    display: 'grid',
    gap: '14px',
  },
  activityRow: {
    display: 'flex',
    gap: '14px',
    padding: '18px',
    borderRadius: '18px',
    border: '1px solid rgba(0,0,0,0.08)',
    backgroundColor: '#FFFFFF',
    alignItems: 'center',
  },
  activityIcon: {
    width: '44px',
    height: '44px',
    borderRadius: '16px',
    backgroundColor: 'rgba(163, 214, 92, 0.16)',
    color: '#2F6B3C',
    display: 'grid',
    placeItems: 'center',
  },
  activityText: {
    display: 'grid',
    gap: '4px',
  },
  activityLabel: {
    fontSize: '15px',
    fontWeight: 700,
    color: '#111827',
  },
  activityTime: {
    fontSize: '13px',
    color: '#64748B',
  },
  buttonRow: {
    display: 'flex',
    gap: '12px',
    flexWrap: 'wrap',
    marginTop: '12px',
  },
  actionButton: {
    padding: '12px 20px',
    borderRadius: '14px',
    border: '1px solid rgba(47, 107, 60, 0.18)',
    backgroundColor: '#FFFFFF',
    color: '#2F6B3C',
    fontWeight: 700,
    cursor: 'pointer',
    transition: 'all 0.2s ease',
    '&:hover': {
      backgroundColor: 'rgba(47, 107, 60, 0.08)',
    },
  },
})

export function ProfilePage() {
  const styles = useStyles()
  const [preferences, setPreferences] = useState(notifications)
  const [sidebarOpen, setSidebarOpen] = useState(true)
  const [activeSection, setActiveSection] = useState<'overview' | 'security' | 'activity'>('overview')

  return (
    <div className={styles.root}>
      <Sidebar open={sidebarOpen} onToggle={() => setSidebarOpen(!sidebarOpen)} />
      <div className={styles.main}>
        <TopNavigation onMenuClick={() => setSidebarOpen(!sidebarOpen)} breadcrumb="Dashboard / Profile" />
        <div className={styles.content}>
          <div className={styles.header}>
            <div className={styles.title}>Supervisor Profile</div>
            <Body1 className={styles.subtitle}>
              Access your account overview, security settings, and notification preferences from a single source of truth.
            </Body1>
          </div>

          <div className={styles.layout}>
            <div className={styles.profileCard}>
              <div className={styles.profileOverview}>
                <div className={styles.avatar}>JM</div>
                <div className={styles.profileMeta}>
                  <div className={styles.profileName}>John Mitchell</div>
                  <div className={styles.profileRole}>Field operations lead</div>
                  <div className={styles.metaItem}>D&G Construction</div>
                  <div className={styles.metaItem}>North Tower Fit-Out</div>
                </div>
              </div>

              <div className={styles.statGrid}>
                {statCards.map((stat) => (
                  <div key={stat.label} className={styles.statCard}>
                    <div className={styles.statLabel}>{stat.label}</div>
                    <div className={styles.statValue}>{stat.value}</div>
                  </div>
                ))}
              </div>

              <Card>
                <CardHeader>
                  <CardTitle>Personal Information</CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="space-y-2">
                    {profileDetails.map((detail) => (
                      <div key={detail.label} className={styles.detailRow}>
                        <div className={styles.detailLabel}>{detail.label}</div>
                        <div className={styles.detailValue}>{detail.value}</div>
                      </div>
                    ))}
                  </div>
                </CardContent>
              </Card>

              <Card>
                <CardHeader>
                  <CardTitle>Employment Details</CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="space-y-2">
                    {accountSummary.map((item) => (
                      <div key={item.label} className={styles.detailRow}>
                        <div className={styles.detailLabel}>{item.label}</div>
                        <div className={styles.detailValue}>{item.value}</div>
                      </div>
                    ))}
                  </div>
                </CardContent>
              </Card>

              <div className={styles.buttonRow}>
                <button className={styles.actionButton}>Edit Profile</button>
                <button className={styles.actionButton}>Change Password</button>
              </div>
            </div>

            <div className={styles.profileCard}>
              <div className="flex flex-wrap gap-2">
                {[
                  { key: 'overview', label: 'Overview' },
                  { key: 'security', label: 'Security' },
                  { key: 'activity', label: 'Activity' },
                ].map((item) => (
                  <button key={item.key} onClick={() => setActiveSection(item.key as 'overview' | 'security' | 'activity')} className={`rounded-full px-3 py-2 text-sm font-semibold transition ${activeSection === item.key ? 'bg-[#2F6B3C] text-white' : 'bg-[#f8fcf5] text-[#2F6B3C]'}`}>
                    {item.label}
                  </button>
                ))}
              </div>

              {activeSection === 'overview' && (
                <div className={styles.preferences}>
                  <div>
                    <div className={styles.statLabel}>Security Settings</div>
                    <div className={styles.detailValue}>Core access controls and password recovery are handled here for the current account.</div>
                  </div>
                  <Card>
                    <CardHeader>
                      <CardTitle>Preferences</CardTitle>
                    </CardHeader>
                    <CardContent>
                      <div className={styles.preferences}>
                        {preferences.map((item, index) => (
                          <div key={item.label} className={styles.preferenceItem}>
                            <div>
                              <div className={styles.detailLabel}>{item.label}</div>
                            </div>
                            <div className={styles.toggle} onClick={() => {
                              const next = [...preferences]
                              next[index] = { ...item, active: !item.active }
                              setPreferences(next)
                            }}>
                              <span className={`${styles.toggleDot} ${item.active ? styles.enabledDot : ''}`} />
                            </div>
                          </div>
                        ))}
                      </div>
                    </CardContent>
                  </Card>
                </div>
              )}

              {activeSection === 'security' && (
                <div className={styles.preferences}>
                  <div>
                    <div className={styles.statLabel}>Account Security</div>
                    <div className={styles.detailValue}>Manage password updates, recovery, and access controls in one compact area.</div>
                  </div>
                  <Card>
                    <CardHeader>
                      <CardTitle>Security Controls</CardTitle>
                    </CardHeader>
                    <CardContent>
                      <div className="space-y-2">
                        <div className={styles.detailRow}>
                          <div className={styles.detailLabel}>Password</div>
                          <div className={styles.detailValue}>Last updated 2 days ago</div>
                        </div>
                        <div className={styles.detailRow}>
                          <div className={styles.detailLabel}>Recovery</div>
                          <div className={styles.detailValue}>Email and mobile recovery enabled</div>
                        </div>
                        <div className={styles.detailRow}>
                          <div className={styles.detailLabel}>Sessions</div>
                          <div className={styles.detailValue}>Active across 2 trusted devices</div>
                        </div>
                      </div>
                    </CardContent>
                  </Card>
                </div>
              )}

              {activeSection === 'activity' && (
                <div>
                  <div className={styles.statLabel}>Recent Activity</div>
                  <div className={styles.activityList}>
                    {activityLog.map((event) => {
                      const Icon = event.icon
                      return (
                        <div key={event.label} className={styles.activityRow}>
                          <div className={styles.activityIcon}>
                            <Icon style={{ fontSize: 20 }} />
                          </div>
                          <div className={styles.activityText}>
                            <div className={styles.activityLabel}>{event.label}</div>
                            <div className={styles.activityTime}>{event.time}</div>
                          </div>
                        </div>
                      )
                    })}
                  </div>
                </div>
              )}
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}
