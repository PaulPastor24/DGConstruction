'use client'

import { useState } from 'react'
import { makeStyles } from '@fluentui/react-components'
import { Sidebar } from './sidebar'
import { TopNavigation } from './top-navigation'
import { AttendanceHeroSection } from './attendance-hero-section'
import { AttendanceSummaryCards } from './attendance-summary-cards'
import { WorkforceAttendanceTable } from './workforce-attendance-table'
import { BiometricVerificationPanel } from './biometric-verification-panel'
import { LateAndAbsentWorkers } from './late-and-absent-workers'
import { AttendanceActivityTimeline } from './attendance-activity-timeline'

const useStyles = makeStyles({
  root: {
    display: 'flex',
    height: '100vh',
  },
  main: {
    flex: 1,
    display: 'flex',
    flexDirection: 'column',
    overflow: 'auto',
  },
  content: {
    flex: 1,
    padding: '40px',
    display: 'flex',
    flexDirection: 'column',
    gap: '40px',
  },
})

export function AttendanceMonitoringPage() {
  const styles = useStyles()
  const [sidebarOpen, setSidebarOpen] = useState(true)

  return (
    <div className={styles.root}>
      <Sidebar open={sidebarOpen} onToggle={() => setSidebarOpen(!sidebarOpen)} />
      <div className={styles.main}>
        <TopNavigation onMenuClick={() => setSidebarOpen(!sidebarOpen)} breadcrumb="Dashboard / Attendance Monitoring" />
        <div className={styles.content}>
          <AttendanceHeroSection />
          <AttendanceSummaryCards />
          <WorkforceAttendanceTable />
          <BiometricVerificationPanel />
          <LateAndAbsentWorkers />
          <AttendanceActivityTimeline />
        </div>
      </div>
    </div>
  )
}
