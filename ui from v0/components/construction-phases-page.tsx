'use client'

import { makeStyles } from '@fluentui/react-components'
import { useState } from 'react'
import { Sidebar } from './sidebar'
import { TopNavigation } from './top-navigation'
import { CurrentConstructionPhase } from './current-construction-phase'
import { PhaseOverviewGrid } from './phase-overview-grid'
import { CurrentPhaseMilestones } from './current-phase-milestones'
import { ScheduleComparison } from './schedule-comparison'
import { RecentConstructionUpdates } from './recent-construction-updates'

const useStyles = makeStyles({
  root: {
    display: 'flex',
    height: '100vh',
  },
  main: {
    flex: 1,
    display: 'flex',
    flexDirection: 'column',
    overflow: 'hidden',
  },
  content: {
    flex: 1,
    overflow: 'auto',
    backgroundColor: '#F5F5F0',
    padding: '40px',
  },
  container: {
    display: 'flex',
    flexDirection: 'column',
    gap: '40px',
    maxWidth: '1600px',
    margin: '0 auto',
  },
  pageHeader: {
    marginBottom: '24px',
  },
  pageTitle: {
    fontSize: '34px',
    fontWeight: 700,
    color: '#2F6B3C',
    marginBottom: '8px',
  },
  pageSubtitle: {
    fontSize: '15px',
    color: '#999999',
    fontWeight: 400,
  },
})

export function ConstructionPhasesPage() {
  const styles = useStyles()
  const [sidebarOpen, setSidebarOpen] = useState(true)

  return (
    <div className={styles.root}>
      <Sidebar open={sidebarOpen} onToggle={() => setSidebarOpen(!sidebarOpen)} />
      <div className={styles.main}>
        <TopNavigation onMenuClick={() => setSidebarOpen(!sidebarOpen)} breadcrumb="Dashboard / Construction Phases" />
        <div className={styles.content}>
          <div className={styles.container}>
            <div className={styles.pageHeader}>
              <div className={styles.pageTitle}>Construction Phases</div>
              <div className={styles.pageSubtitle}>Monitor construction execution across all project phases.</div>
            </div>

            <CurrentConstructionPhase />
            <PhaseOverviewGrid />
            <CurrentPhaseMilestones />
            <ScheduleComparison />
            <RecentConstructionUpdates />
          </div>
        </div>
      </div>
    </div>
  )
}
